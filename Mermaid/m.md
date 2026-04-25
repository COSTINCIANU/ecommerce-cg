# C.G Boutique — Diagrammes de Séquences en Mermaid

**Projet :** C.G Boutique — E-commerce mode (solo)  
**Stack :** Symfony 7.4 · PHP 8.2 · MySQL/MariaDB · EasyAdmin 5 · Stripe · PayPal · Mailjet · DomPDF  
**Auteure :** Gheorghina COSTINCIANU — ADRAR Formation

---

## DS-01 · Inscription Utilisateur

> **Route :** `POST /register` → SecurityController → Doctrine → user (BDD) → Mailjet

```mermaid
sequenceDiagram
    actor Client as 👤 Client (Navigateur)
    participant SC as SecurityController<br/>/register
    participant ORM as Doctrine ORM<br/>(EntityManager)
    participant DB as user<br/>(BDD MySQL)
    participant MJ as Mailjet API

    Client->>SC: POST /register { email, password, prenom, nom, _csrf_token }

    alt Token CSRF invalide
        SC-->>Client: 🔴 302 /register + Flash "Token invalide"
    end

    alt Email déjà utilisé
        SC->>DB: SELECT FROM user WHERE email = ?
        DB-->>SC: Résultat existant
        SC-->>Client: 🔴 302 /register + Flash "Cet email est déjà utilisé"
    end

    Note over SC: password_hash(bcrypt)<br/>roles = ["ROLE_USER"]

    SC->>ORM: persist(new User) + flush()
    ORM->>DB: INSERT INTO user (email, password, roles, created_at)

    alt PDOException / Erreur BDD
        DB-->>ORM: 🔴 PDOException
        ORM-->>SC: Exception propagée
        SC-->>Client: 🔴 500 + Flash "Erreur serveur, réessayez"
    end

    DB-->>ORM: OK — user.id retourné
    ORM-->>SC: User persisté

    SC->>MJ: POST /v3.1/send { to: email, template: confirmation_inscription }

    alt Mailjet erreur 4xx/5xx — sender non vérifié
        MJ-->>SC: 🔴 4xx/5xx — email non envoyé
        Note over SC: Log erreur Mailjet (non bloquant)<br/>💡 Fix : sender → Gmail vérifié<br/>User créé quand même en BDD
    else Email envoyé
        MJ-->>SC: ✅ 200 OK { "Status": "success" }
    end

    SC-->>Client: ✅ 302 → /login + Flash "Compte créé, vérifiez vos emails"
```

**Tables :** `user` · **Fix :** sender Mailjet → contact.cgboutique@gmail.com · **CSRF :** Symfony Security

---

## DS-02 · Connexion Utilisateur (Login)

> **Route :** `POST /login` → Symfony Security → UserProvider → Session

```mermaid
sequenceDiagram
    actor Client as 👤 Client (Navigateur)
    participant SEC as Symfony Security<br/>(Authenticator)
    participant UP as UserProvider<br/>(Doctrine)
    participant DB as user<br/>(BDD MySQL)
    participant SESS as Session<br/>(PHPSESSID)

    Client->>SEC: POST /login { _username: email, _password: password, _csrf_token }

    alt Token CSRF invalide
        SEC-->>Client: 🔴 302 /login + "Invalid CSRF token"
    end

    SEC->>UP: loadUserByIdentifier(email)
    UP->>DB: SELECT * FROM user WHERE email = ? LIMIT 1

    alt Utilisateur non trouvé
        DB-->>UP: NULL — aucune ligne
        UP-->>SEC: 🔴 UserNotFoundException
        SEC-->>Client: 🔴 302 /login + "Identifiants invalides"
    end

    DB-->>UP: User { id, email, password_hash, roles }
    UP-->>SEC: UserInterface objet retourné

    Note over SEC: password_verify(inputPassword, hash_bcrypt)<br/>Vérification en temps constant — anti timing-attack

    alt password_verify → false
        SEC-->>Client: 🔴 302 /login + "Identifiants invalides"
    end

    SEC->>SESS: createAuthenticatedToken(User) + Set-Cookie PHPSESSID
    SESS-->>SEC: Token stocké en session
    SEC-->>Client: ✅ 302 → /compte + Cookie PHPSESSID
```

**Table :** `user` · **Firewall :** main · **CSRF :** form_login activé · **Session :** NativeSessionStorage

---

## DS-03 · Ajout Produit au Panier

> **Route :** `POST /cart/add/{id}` → CartController → CartService → cart / cart_item

```mermaid
sequenceDiagram
    actor Client as 👤 Client (Navigateur)
    participant CC as CartController<br/>/cart/add/{id}
    participant CS as CartService
    participant P as product<br/>(BDD)
    participant DB as cart / cart_item<br/>(BDD)
    participant SESS as Session<br/>Symfony

    Client->>CC: POST /cart/add/{productId} + CSRF token

    alt Utilisateur non connecté
        CC-->>Client: 🔴 302 → /login (access_denied Symfony)
    end

    CC->>CS: findProduct(id)
    CS->>P: SELECT * FROM product WHERE id = ? AND isActive = 1

    alt Produit non trouvé ou inactif
        P-->>CS: NULL retourné
        CS-->>CC: 🔴 EntityNotFoundException
        CC-->>Client: 🔴 404 + Flash "Produit introuvable"
    end

    P-->>CS: Product { id, name, price, stock, isActive }

    alt stock = 0 — rupture
        CS-->>CC: 🔴 StockException
        CC-->>Client: 🔴 Flash "Ce produit est épuisé"
    end

    CS->>DB: SELECT cart WHERE user_id = ? AND status = "active"
    DB-->>CS: Cart existant OU NULL

    Note over CS: Si NULL → INSERT cart (user_id, status="active")<br/>Sinon récupère le cart existant

    CS->>DB: INSERT cart_item (cart_id, product_id, quantity=1)<br/>ON DUPLICATE KEY UPDATE quantity + 1
    Note over CS: 📌 BatchQuery — évite N+1<br/>31s → 1.3s

    DB-->>CS: OK — cart_item enregistré
    CS->>SESS: update session cart_count += 1
    CS-->>CC: CartItem créé avec succès
    CC-->>Client: ✅ 302 → /cart + Flash "Produit ajouté au panier"
```

**Tables :** `cart`, `cart_item`, `product` · **Optimisation :** BatchQuery (31s → 1.3s)

---

## DS-04 · Paiement Stripe Checkout + Webhook

> **Route :** `POST /order/checkout` → StripeService → Stripe API → Webhook → order (isPaid=1)

```mermaid
sequenceDiagram
    actor Client as 👤 Client (Navigateur)
    participant OC as OrderController<br/>/checkout
    participant SS as StripeService<br/>(SDK PHP)
    participant STR as Stripe API<br/>(externe)
    participant WH as WebhookController<br/>/stripe/webhook
    participant DB as order<br/>(BDD MySQL)
    participant MJ as Mailjet API

    Client->>OC: POST /order/checkout { cart_id, adresse_livraison }

    alt Panier vide ou expiré
        OC-->>Client: 🔴 302 /cart + Flash "Votre panier est vide"
    end

    OC->>DB: INSERT order { user_id, total, status="pending", isPaid=0, created_at }
    DB-->>OC: order.id = 42

    OC->>SS: createCheckoutSession(order, cartItems)
    SS->>STR: POST /v1/checkout/sessions { line_items, success_url, cancel_url, metadata: { order_id: 42 } }

    alt StripeException — timeout / API key invalide / 4xx
        STR-->>SS: 🔴 StripeException / ConnectionException
        SS-->>OC: Exception propagée
        OC->>DB: UPDATE order SET status="failed"
        OC-->>Client: 🔴 500 + Flash "Erreur paiement, réessayez"
    end

    STR-->>SS: 200 { session.id, session.url }
    SS-->>OC: checkoutSession objet
    OC-->>Client: 302 → session.url (page CB Stripe PCI-DSS)

    Note over Client: Client saisit coordonnées CB<br/>sur page Stripe sécurisée<br/>Stripe gère le 3DS si nécessaire

    STR->>WH: POST /stripe/webhook { event: checkout.session.completed, Stripe-Signature: … }

    alt Signature webhook invalide
        WH-->>STR: 🔴 400 Bad Request "Invalid signature"
    end

    WH->>DB: UPDATE order SET isPaid=1, status="paid", stripe_session_id=? WHERE id=42

    alt PDOException — erreur BDD
        DB-->>WH: 🔴 PDOException
        Note over WH: Log erreur — Stripe relancera le webhook<br/>💡 Prévoir idempotence via stripe_session_id
    end

    DB-->>WH: OK — 1 row updated
    WH->>MJ: POST /v3.1/send { confirmation commande + PDF facture DomPDF }
    MJ-->>WH: 200 OK / erreur non bloquante
    WH-->>STR: ✅ 200 OK — acquittement webhook (obligatoire < 30s)

    Client->>OC: GET /order/success?session_id=cs_xxx
    OC-->>Client: ✅ 200 — Page "Commande confirmée" + N° commande
```

**Tables :** `order` (isPaid BOOLEAN, status VARCHAR) · **Env :** STRIPE_SECRET_KEY, STRIPE_WEBHOOK_SECRET

---

## DS-05 · Paiement PayPal (JS SDK)

> **Route :** `GET /order/paypal` → PayPal JS SDK → PayPal API → PayPalController → order (isPaid=1)

```mermaid
sequenceDiagram
    actor Client as 👤 Client (Navigateur)
    participant PC as PayPalController<br/>/order/paypal
    participant PJS as PayPal JS SDK<br/>(frontend)
    participant PP as PayPal API<br/>(externe)
    participant DB as order<br/>(BDD MySQL)
    participant MJ as Mailjet API

    Client->>PC: GET /order/paypal
    PC-->>Client: 200 — paypal.html.twig (bouton PayPal + PAYPAL_CLIENT_ID)

    Note over Client: 💡 Fix : DOMContentLoaded guard appliqué<br/>TypeError dataset corrigé<br/>Client clique "Payer avec PayPal"

    Client->>PJS: createOrder() déclenché
    PJS->>PP: POST /v2/checkout/orders { intent: CAPTURE, amount, currency: EUR }

    alt PayPal API erreur — réseau / paramètres invalides
        PP-->>PJS: 🔴 error / UNPROCESSABLE_ENTITY
        PJS-->>Client: 🔴 Erreur inline JS
        Client->>PC: POST /paypal/cancel → UPDATE order status="cancelled"
    end

    PP-->>PJS: 201 { id: ORDER_ID, status: CREATED }

    Note over Client: Popup PayPal ouverte<br/>Client se connecte et approuve

    alt Client ferme la popup sans payer
        PJS-->>Client: 🔴 onCancel() — "Paiement annulé"
    end

    Client->>PJS: onApprove(data) { orderID: ORDER_ID }
    PJS->>PP: POST /v2/checkout/orders/{ORDER_ID}/capture

    alt Capture refusée — INSTRUMENT_DECLINED
        PP-->>PJS: 🔴 INSTRUMENT_DECLINED / 422
        PJS-->>Client: 🔴 "Paiement refusé, vérifiez votre compte PayPal"
    end

    PP-->>PJS: 200 { status: COMPLETED, capture_id: CAP_xxx }
    PJS->>PC: POST /paypal/capture { orderID, captureID }

    PC->>DB: UPDATE order SET isPaid=1, status="paid", paypal_order_id=ORDER_ID WHERE id=?
    DB-->>PC: OK — 1 row updated
    PC->>MJ: POST /v3.1/send { email confirmation commande }
    MJ-->>PC: 200 OK / erreur non bloquante
    PC-->>Client: ✅ 200 JSON { status: "success" } → redirect /order/success
```

**Table :** `order` · **Fix :** DOMContentLoaded guard · **Env :** PAYPAL_CLIENT_ID, PAYPAL_SECRET

---

## DS-06 · Réinitialisation du Mot de Passe

> **Route :** `POST /reset-password` → ResetPasswordController → reset_password_request → Mailjet

```mermaid
sequenceDiagram
    actor Client as 👤 Client (Navigateur)
    participant RPC as ResetPassword<br/>Controller
    participant RPH as ResetPassword<br/>Helper (Bundle)
    participant DB as reset_password<br/>_request (BDD)
    participant MJ as Mailjet<br/>(sender Gmail)

    Client->>RPC: POST /reset-password { email }

    Note over RPC: 📌 Anti-énumération :<br/>même réponse si email existe ou non

    RPC->>RPH: processSendingPasswordResetEmail(email)
    RPH->>DB: SELECT WHERE user.email = ? AND expiresAt > NOW()

    alt Token valide non expiré — throttling anti-spam
        DB-->>RPH: Token existant retourné
        Note over RPH: Réutilisation du token<br/>Pas de nouveau INSERT
    else Aucun token valide
        RPH->>DB: INSERT reset_password_request { token_hash HMAC, expiresAt=+1h, user_id }
        DB-->>RPH: OK — request créée
    end

    RPH-->>RPC: plainTextToken (signé, usage unique)
    RPC->>MJ: POST /v3.1/send { from: contact.cgboutique@gmail.com, lien reset + token }

    alt Mailjet erreur — sender non vérifié / 4xx
        MJ-->>RPC: 🔴 4xx "Sender not verified"
        Note over RPC: 💡 Fix : sender switché vers Gmail vérifié<br/>contact.cgboutique@gmail.com
    else Email envoyé
        MJ-->>RPC: ✅ 200 OK { "Status": "success" }
    end

    RPC-->>Client: 302 → /reset-password/check-email (même message — anti-énumération)

    Note over Client: Client reçoit l'email<br/>et clique sur le lien

    Client->>RPC: GET /reset-password/reset/{token}
    RPC->>RPH: validateTokenAndFetchUser(token)

    alt Token expiré / invalide / déjà utilisé
        RPH-->>RPC: 🔴 InvalidResetPasswordTokenException
        RPC-->>Client: 🔴 302 /reset-password + Flash "Lien expiré, refaire la demande"
    end

    Client->>RPC: POST /reset-password/reset/{token} { newPassword, confirmPassword }
    RPC->>DB: UPDATE user SET password=hash(new) + DELETE reset_password_request WHERE user_id=?
    RPC-->>Client: ✅ 302 → /login + Flash "Mot de passe modifié avec succès"
```

**Tables :** `reset_password_request`, `user` · **Bundle :** symfonycasts/reset-password-bundle · **Fix :** sender Gmail

---

## DS-07 · Administration Commandes — EasyAdmin 5

> **Route :** `/admin` → EasyAdmin OrderCrudController → order → Mailjet

```mermaid
sequenceDiagram
    actor Admin as 🔑 Admin (Navigateur)
    participant EA as EasyAdmin<br/>OrderCrudController
    participant ORM as Doctrine ORM<br/>(EntityManager)
    participant DB as order<br/>(BDD MySQL)
    participant MJ as Mailjet API

    Admin->>EA: GET /admin?crudAction=index&crudControllerFqcn=OrderCrudController

    alt Non authentifié ou ROLE_ADMIN manquant
        EA-->>Admin: 🔴 403 Forbidden / 302 → /login (Access Denied)
    end

    EA->>ORM: createQueryBuilder → findAll(Order) + pagination
    ORM->>DB: SELECT * FROM order ORDER BY created_at DESC LIMIT 20 OFFSET 0
    DB-->>ORM: Collection<Order>
    ORM-->>EA: OrderCollection paginée
    EA-->>Admin: 200 — Liste commandes (ChoiceField status, BooleanField isPaid)

    Admin->>EA: POST /admin?crudAction=edit&entityId=42 { status="shipped", isPaid=1 }

    alt CSRF invalide ou valeur status hors ChoiceField
        EA-->>Admin: 🔴 422 + EasyAdmin form error inline
    end

    EA->>ORM: persist(Order { status="shipped", isPaid=true }) + flush()
    ORM->>DB: UPDATE order SET status="shipped", isPaid=1 WHERE id=42

    alt PDOException — deadlock / contrainte FK
        DB-->>ORM: 🔴 PDOException
        ORM-->>EA: Exception propagée
        EA-->>Admin: 🔴 500 + Flash "Erreur serveur"
    end

    DB-->>ORM: OK — 1 row updated
    EA->>MJ: POST /v3.1/send { to: client.email, "Votre commande est expédiée" }
    MJ-->>EA: 200 OK / erreur non bloquante
    EA-->>Admin: ✅ 302 /admin + Flash "Commande mise à jour avec succès"
```

**Table :** `order` · **ChoiceField :** pending/paid/shipped/delivered/cancelled · **BooleanField :** isPaid

---

## DS-08 · Gestion Wishlist (Toggle Add/Remove)

> **Route :** `POST /wishlist/toggle/{id}` → WishListController → WishListRepository → wish_list

```mermaid
sequenceDiagram
    actor Client as 👤 Client (Navigateur)
    participant WC as WishListController<br/>/wishlist/toggle
    participant WR as WishListRepository<br/>(Doctrine)
    participant DB as wish_list<br/>(BDD MySQL)

    Client->>WC: POST /wishlist/toggle/{productId} + CSRF token

    alt Utilisateur non connecté
        WC-->>Client: 🔴 401 JSON { error: "Connectez-vous pour gérer votre liste de souhaits" }
    end

    WC->>WR: findOneBy({ user: userId, product: productId })
    WR->>DB: SELECT * FROM wish_list WHERE user_id = ? AND product_id = ? LIMIT 1
    DB-->>WR: WishList entity OU NULL
    WR-->>WC: WishList OU NULL retourné

    alt WishList trouvée → supprimer (toggle OFF)
        WC->>WR: remove(wishList) + flush()
        WR->>DB: DELETE FROM wish_list WHERE id = ?
        DB-->>WR: OK — 1 row deleted
        WC-->>Client: 200 JSON { status: "removed", icon: "☆", message: "Retiré de votre wishlist" }
    else NULL → ajouter (toggle ON)
        WC->>WR: persist(new WishList { user, product, createdAt }) + flush()
        WR->>DB: INSERT INTO wish_list (user_id, product_id, created_at)
        DB-->>WR: OK — new id retourné
        WC-->>Client: ✅ 200 JSON { status: "added", icon: "★", count: N, message: "Ajouté à votre wishlist" }
    end
```

**Table :** `wish_list` (user_id FK, product_id FK, created_at) · **Réponse :** JSON AJAX · **Icône :** ★/☆ temps réel

---

## Légende

| Symbole Mermaid | Signification UML |
|---|---|
| `Client->>SC:` | Message synchrone (flèche pleine) — appel |
| `SC-->>Client:` | Message de retour (flèche pointillée) — réponse |
| `alt ... else ... end` | Combined Fragment **alt** — cas alternatifs |
| `Note over X:` | Commentaire / annotation sur une lifeline |
| `actor` | Acteur externe (utilisateur humain) |
| `participant` | Système / composant interne |
| 🔴 préfixe | Chemin d'erreur — exception ou validation échouée |
| ✅ préfixe | Chemin nominal — succès |