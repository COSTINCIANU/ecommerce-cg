
<!-- Diagramme d’activité -->
flowchart TD
    A[Début] --> B[Remplir formulaire]
    B --> C{Formulaire valide ?}

    C -->|Oui| D[Enregistrer message]
    C -->|Non| E[Erreur formulaire]

    D --> F{Enregistrement OK ?}

    F -->|Oui| G[Notifier admin]
    F -->|Non| H[Erreur enregistrement]

    G --> I[Lire message]
    I --> J[Rédiger réponse]
    J --> K{Réponse valide ?}

    K -->|Oui| L[Envoyer email]
    K -->|Non| J

    L --> M{Email envoyé ?}

    M -->|Oui| N[Message marqué répondu]
    M -->|Non| O[Erreur email]

    N --> P[Fin]
    E --> P
    H --> P
    O --> P
<!-- Fin Diagramme d’activité -->

<!-- Ajout au panier -->
sequenceDiagram
    actor Client
    participant Browser
    participant CartController
    participant CartService
    participant ProductRepository
    participant Session

    Client ->> Browser : clicAjouterAuPanier
    Browser ->> CartController : GET /cart/add/{id}/{qty}
    CartController ->> CartService : add(productId, qty)
    CartService ->> ProductRepository : find(productId)

    alt Produit trouvé
        ProductRepository -->> CartService : product
        CartService ->> Session : saveCartData
        CartService -->> CartController : updatedCart
        CartController -->> Browser : JSON success
        Browser -->> Client : message succès
    else Produit introuvable
        CartService -->> CartController : erreur produit
        CartController -->> Browser : JSON error
        Browser -->> Client : afficher erreur
    end
<!-- fin Ajout au panier -->

<!-- Paiment Stripe  -->

sequenceDiagram
    actor Client
    participant Browser
    participant StripeController
    participant StripeAPI
    participant EntityManager

    Client ->> Browser : choisirStripe
    Browser ->> StripeController : POST /api/stripe/intent
    StripeController ->> StripeAPI : createPaymentIntent

    alt Intent OK
        StripeAPI -->> StripeController : client_secret
        StripeController -->> Browser : client_secret
        Browser ->> StripeAPI : confirmCardPayment

        alt Paiement accepté
            StripeAPI -->> Browser : success
            Browser ->> StripeController : confirm
            StripeController ->> EntityManager : order.isPaid = true
            EntityManager -->> StripeController : saved
            StripeController -->> Browser : success
            Browser -->> Client : page confirmation
        else Paiement refusé
            StripeAPI -->> Browser : failed
            Browser -->> Client : erreur paiement
        end

    else Erreur création intent
        StripeController -->> Browser : error
        Browser -->> Client : erreur technique
    end
<!-- Fin Paiment Stripe  -->

<!-- Validation commande -->
sequenceDiagram
    actor Client
    participant Browser
    participant OrderController
    participant CartService
    participant EntityManager
    participant Mailer

    Client ->> Browser : validerCommande
    Browser ->> OrderController : POST /checkout/validate
    OrderController ->> CartService : getCartDetails

    alt Panier valide
        CartService -->> OrderController : cartData

        OrderController ->> EntityManager : persist order
        OrderController ->> EntityManager : persist orderDetails

        alt Sauvegarde OK
            EntityManager -->> OrderController : saved
            OrderController ->> CartService : clear

            OrderController ->> Mailer : sendEmail

            alt Email envoyé
                Mailer -->> Client : confirmation
            else Erreur email
                Mailer -->> OrderController : erreur
            end

            OrderController -->> Browser : redirect
            Browser -->> Client : page confirmation

        else Erreur base
            EntityManager -->> OrderController : error
            OrderController -->> Browser : error
            Browser -->> Client : afficher erreur
        end

    else Panier invalide
        OrderController -->> Browser : error
        Browser -->> Client : afficher erreur
    end
<!-- Fin Validation commande -->


<!-- Parcours client global -->
sequenceDiagram
    actor Client
    participant Site
    participant Panier
    participant Commande
    participant Paiement

    Client ->> Site : consulterProduits
    Client ->> Panier : ajouterProduit
    Client ->> Panier : validerPanier

    alt Panier valide
        Site -->> Client : demanderAdresse

        alt Adresse valide
            Client ->> Commande : confirmerCommande
            Commande ->> Paiement : creerPaiement

            alt Paiement accepté
                Paiement -->> Commande : OK
                Commande -->> Client : confirmation
            else Paiement refusé
                Paiement -->> Client : erreur paiement
            end

        else Adresse invalide
            Site -->> Client : erreur adresse
        end

    else Panier invalide
        Panier -->> Client : erreur panier
    end
<!-- Fin Parcours client global -->

<!-- Contact / réponse admin -->
sequenceDiagram
    actor Client
    actor Admin
    participant Contact
    participant Mailer

    Client ->> Contact : envoyerMessage

    alt Message valide
        Contact ->> Admin : notification
        Admin ->> Contact : redigerReponse

        alt Réponse prête
            Contact ->> Mailer : envoyerEmail

            alt Email envoyé
                Mailer -->> Client : recevoirRéponse
            else Erreur envoi
                Mailer -->> Contact : erreur
            end

        else Pas de réponse
            Contact -->> Admin : attente
        end

    else Message invalide
        Contact -->> Client : erreur formulaire
    end
<!-- Contact / réponse admin -->




<!-- Diagramme d’activité — Contact / réponse admin avec couleurs -->
flowchart TD

    A[Début] --> B[Client remplit le formulaire]
    B --> C{Formulaire valide ?}

    C -->|Oui| D[Message enregistré]
    C -->|Non| E[Afficher erreur formulaire]

    D --> F{Enregistrement réussi ?}

    F -->|Oui| G[Notification administrateur]
    F -->|Non| H[Afficher erreur enregistrement]

    G --> I[Administrateur lit le message]
    I --> J[Administrateur rédige une réponse]
    J --> K{Réponse valide ?}

    K -->|Oui| L[Réponse envoyée par email]
    K -->|Non| J

    L --> M{Email envoyé ?}

    M -->|Oui| N[Message marqué comme répondu]
    M -->|Non| O[Afficher erreur email]

    N --> P[Fin]
    E --> P
    H --> P
    O --> P

    style A fill:#a3e635,stroke:#333,stroke-width:2px
    style B fill:#93c5fd,stroke:#333,stroke-width:1px
    style C fill:#fcd34d,stroke:#333,stroke-width:1px
    style D fill:#34d399,stroke:#333,stroke-width:1px
    style E fill:#f87171,stroke:#333,color:#fff,stroke-width:1px
    style F fill:#fcd34d,stroke:#333,stroke-width:1px
    style G fill:#93c5fd,stroke:#333,stroke-width:1px
    style H fill:#f87171,stroke:#333,color:#fff,stroke-width:1px
    style I fill:#93c5fd,stroke:#333,stroke-width:1px
    style J fill:#93c5fd,stroke:#333,stroke-width:1px
    style K fill:#fcd34d,stroke:#333,stroke-width:1px
    style L fill:#34d399,stroke:#333,stroke-width:1px
    style M fill:#fcd34d,stroke:#333,stroke-width:1px
    style N fill:#34d399,stroke:#333,stroke-width:1px
    style O fill:#f87171,stroke:#333,color:#fff,stroke-width:1px
    style P fill:#4ade80,stroke:#333,stroke-width:2px

<!-- Fin Diagramme d’activité — Contact / réponse admin avec couleurs -->

<!-- Séquence — Ajouter au panier -->
sequenceDiagram
    autonumber
    actor Client
    participant Browser
    participant CartController
    participant CartService
    participant ProductRepository
    participant Session

    rect rgb(230, 240, 255)
    Client->>Browser: clicAjouterAuPanier()
    Browser->>CartController: GET /cart/add/{id}/{qty}
    CartController->>CartService: add(productId, qty)
    CartService->>ProductRepository: find(productId)
    end

    alt Produit trouvé
        rect rgb(220, 252, 231)
        ProductRepository-->>CartService: product
        CartService->>Session: saveCartData()
        CartService-->>CartController: updatedCart
        CartController-->>Browser: JSON success
        Browser-->>Client: message succès
        end
    else Produit introuvable
        rect rgb(254, 226, 226)
        CartService-->>CartController: erreur produit
        CartController-->>Browser: JSON error
        Browser-->>Client: afficher erreur
        end
    else Stock insuffisant
        rect rgb(254, 226, 226)
        CartService-->>CartController: erreur stock
        CartController-->>Browser: JSON error
        Browser-->>Client: afficher stock insuffisant
        end
    end
<!-- fin Séquence — Ajouter au panier -->


<!-- Séquence — Paiement Stripe -->
sequenceDiagram
    autonumber
    actor Client
    participant Browser
    participant StripeController
    participant StripeAPI
    participant EntityManager

    rect rgb(230, 240, 255)
    Client->>Browser: choisirStripe()
    Browser->>StripeController: POST /api/stripe/intent
    StripeController->>StripeAPI: createPaymentIntent(amount)
    end

    alt Intent créé
        rect rgb(220, 252, 231)
        StripeAPI-->>StripeController: client_secret
        StripeController-->>Browser: client_secret
        Browser->>StripeAPI: confirmCardPayment(client_secret)
        end

        alt Paiement accepté
            rect rgb(220, 252, 231)
            StripeAPI-->>Browser: payment success
            Browser->>StripeController: POST /api/stripe/confirm
            StripeController->>EntityManager: order.isPaid = true
            EntityManager-->>StripeController: saved
            StripeController-->>Browser: success response
            Browser-->>Client: page confirmation
            end
        else Paiement refusé
            rect rgb(254, 226, 226)
            StripeAPI-->>Browser: payment failed
            Browser-->>Client: afficher erreur paiement
            end
        end
    else Erreur création intent
        rect rgb(254, 226, 226)
        StripeController-->>Browser: error response
        Browser-->>Client: afficher erreur technique
        end
    end
<!-- Fin Séquence — Paiement Stripe -->

<!-- Séquence — Validation commande -->
sequenceDiagram
    autonumber
    actor Client
    participant Browser
    participant OrderController
    participant CartService
    participant EntityManager
    participant Mailer

    rect rgb(230, 240, 255)
    Client->>Browser: validerCommande()
    Browser->>OrderController: POST /checkout/validate
    OrderController->>CartService: getCartDetails()
    end

    alt Panier valide
        rect rgb(220, 252, 231)
        CartService-->>OrderController: cartData
        OrderController->>EntityManager: persist(order)
        OrderController->>EntityManager: persist(orderDetails)
        end

        alt Sauvegarde OK
            rect rgb(220, 252, 231)
            EntityManager-->>OrderController: saved
            OrderController->>CartService: clear()
            OrderController->>Mailer: sendConfirmationEmail()
            end

            alt Email envoyé
                rect rgb(220, 252, 231)
                Mailer-->>Client: email confirmation
                OrderController-->>Browser: redirect confirmation
                Browser-->>Client: page confirmation
                end
            else Erreur email
                rect rgb(254, 243, 199)
                Mailer-->>OrderController: erreur email
                OrderController-->>Browser: redirect confirmation
                Browser-->>Client: page confirmation
                end
            end
        else Erreur base de données
            rect rgb(254, 226, 226)
            EntityManager-->>OrderController: save failed
            OrderController-->>Browser: erreur validation
            Browser-->>Client: afficher erreur
            end
        end
    else Panier invalide
        rect rgb(254, 226, 226)
        OrderController-->>Browser: erreur panier
        Browser-->>Client: afficher erreur panier
        end
    end
<!-- Séquence — Validation commande -->

<!-- Séquence — Parcours client global -->
sequenceDiagram
    autonumber
    actor Client
    participant Site
    participant Panier
    participant Commande
    participant Paiement

    rect rgb(230, 240, 255)
    Client->>Site: consulterProduits()
    Client->>Panier: ajouterProduit()
    Client->>Panier: validerPanier()
    end

    alt Panier valide
        rect rgb(220, 252, 231)
        Site-->>Client: demanderAdresseEtTransporteur()
        end

        alt Adresse et transporteur valides
            rect rgb(220, 252, 231)
            Client->>Commande: confirmerCommande()
            Commande->>Paiement: creerPaiement()
            end

            alt Paiement accepté
                rect rgb(220, 252, 231)
                Paiement-->>Commande: paiementAccepte()
                Commande-->>Client: confirmationCommande()
                end
            else Paiement refusé
                rect rgb(254, 226, 226)
                Paiement-->>Client: afficher erreur paiement
                end
            end
        else Données invalides
            rect rgb(254, 226, 226)
            Site-->>Client: afficher erreur adresse/transporteur
            end
        end
    else Panier invalide
        rect rgb(254, 226, 226)
        Panier-->>Client: afficher erreur panier
        end
    end
<!-- Fin Séquence — Parcours client global -->

<!-- Séquence — Contact / réponse admin -->
sequenceDiagram
    autonumber
    actor Client
    actor Admin
    participant Contact
    participant Mailer

    rect rgb(230, 240, 255)
    Client->>Contact: envoyerMessage()
    end

    alt Message valide
        rect rgb(220, 252, 231)
        Contact->>Admin: notificationNouveauMessage
        Admin->>Contact: redigerReponse()
        end

        alt Réponse prête
            rect rgb(220, 252, 231)
            Contact->>Mailer: envoyerEmailReponse()
            end

            alt Email envoyé
                rect rgb(220, 252, 231)
                Mailer-->>Client: recevoirReponse()
                end
            else Email échoué
                rect rgb(254, 226, 226)
                Mailer-->>Contact: erreur envoi
                end
            end
        else Pas de réponse
            rect rgb(254, 243, 199)
            Contact-->>Admin: message en attente
            end
        end
    else Message invalide
        rect rgb(254, 226, 226)
        Contact-->>Client: afficher erreur formulaire
        end
    end
<!-- Fin Séquence — Contact / réponse admin -->


<!-- 

Couleurs utilisées
Bleu clair : début de flux / appel principal
Vert clair : succès / cas nominal
Rouge clair : erreur / échec
Jaune clair : attente / cas intermédiaire non bloquant

 -->


<!-- Diagramme de Class sans couleurs  -->
classDiagram

class user {
  - id: int
  - email: string
  - roles: string
  - password: string
  - full_name: string
  - civility: string
  - created_at: Date
  - updated_at: Date
  - is_verified: boolean
}

class address {
  - id: int
  - name: string
  - client_name: string
  - street: string
  - code_postal: string
  - city: string
  - state: string
  - more_details: string
  - updated_at: Date
  - created_at: Date
  - user_id: int
  - address_type: string
}

class comment {
  - id: int
  - rating: int
  - content: string
  - email: string
  - is_published: boolean
  - created_at: Date
  - author_id: int
  - product_id: int
}

class product {
  - id: int
  - name: string
  - slug: string
  - description: string
  - more_description: string
  - additional_infos: string
  - stock: int
  - solde_price: int
  - regular_price: int
  - image_urls: string
  - brand: string
  - is_available: boolean
  - is_best_seller: boolean
  - is_new_arrival: boolean
  - is_featured: boolean
  - is_special_offer: boolean
  - created_at: Date
  - updated_at: Date
}

class category {
  - id: int
  - name: string
  - slug: string
  - description: string
  - is_mega: boolean
  - created_at: Date
  - updated_at: Date
  - image_url: string
}

class order {
  - id: int
  - client_name: string
  - quantity: int
  - taxe: int
  - order_cost_ttc: int
  - order_cost_ht: int
  - status: string
  - is_paid: boolean
  - carrier_id: int
  - carrier_price: int
  - carrier_name: string
  - billing_address: string
  - shipping_address: string
  - payment_method: string
  - stripe_client_secret: string
  - paypal_client_secret: string
  - updated_at: Date
  - created_at: Date
  - user_id: int
}

class order_details {
  - id: int
  - product_name: string
  - product_description: string
  - product_solde_price: int
  - product_regular_price: int
  - quantity: int
  - taxe: int
  - subtotal: int
  - my_order_id: int
  - updated_at: Date
  - created_at: Date
}

class carrier {
  - id: int
  - name: string
  - description: string
  - price: int
  - created_at: Date
  - updated_at: Date
}

class payment_method {
  - id: int
  - name: string
  - description: string
  - more_description: string
  - image_url: string
  - test_public_api_key: string
  - test_private_api_key: string
  - prod_public_api_key: string
  - prod_private_api_key: string
  - updated_at: Date
  - created_at: Date
  - test_base_url: string
  - prod_base_url: string
}

class product_category {
  - product_id: int
  - category_id: int
}

class product_related_products {
  - product_source: int
  - product_target: int
}

user "1" o-- "0..*" address : posseder
user "1" --> "0..*" comment : ecrire
product "1" --> "0..*" comment : recevoir
user "1" --> "0..*" order : passer
order "1" *-- "1..*" order_details : contenir

product "1" --> "0..*" product_category : lier
category "1" --> "0..*" product_category : lier
product "0..*" --> "0..*" category : appartenir_a

product "1" --> "0..*" product_related_products : source
product "1" --> "0..*" product_related_products : target
product "0..*" --> "0..*" product : etre_lie_a

order ..> carrier : utiliser
order ..> payment_method : utiliser

<!-- Fin Diagramme de Class sans couleurs  -->

<!-- Diagramme Class Use as Address -->
classDiagram

class user {
  - id: int
  - email: string
  - password: string
}

class address {
  - id: int
  - street: string
  - city: string
  - user_id: int
}

user "1" --> "0..*" address : posseder

<!-- Fin Diagramme Class Use as Address -->

<!-- Diagramme Class Use as Order -->
classDiagram
class user
class order
user "1" --> "0..*" order : passer
<!-- Fin  Diagramme Class Use as Order -->