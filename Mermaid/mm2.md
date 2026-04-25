# C.G Boutique — Diagramme de Classes (Mermaid)

**Projet :** C.G Boutique — E-commerce mode (solo)  
**Stack :** Symfony 7.4 · PHP 8.2 · MySQL/MariaDB · Doctrine ORM  
**Source :** Basé sur le schéma SQL réel `ecommerce-cg.sql`  
**Certification :** CDA — RNCP37873 Niveau 6  
**Auteure :** Gheorghina COSTINCIANU — ADRAR Formation

---

## Diagramme de Classes Complet

```mermaid
classDiagram

    %% ─────────────────────────────────────────────
    %% CLASSES PRINCIPALES
    %% ─────────────────────────────────────────────

    class User {
        +int id PK
        +string email UNIQUE
        +string password
        +array roles
        +string full_name
        +string civility
        +bool is_verified
        +datetime created_at
        +datetime updated_at
    }

    class Address {
        +int id PK
        +string name
        +string client_name
        +string street
        +string code_postal
        +string city
        +string state
        +string address_type
        +string more_details
        +datetime created_at
        +datetime updated_at
        +int user_id FK
    }

    class Product {
        +int id PK
        +string name
        +string slug UNIQUE
        +string description
        +string more_description
        +string additional_infos
        +int stock
        +int solde_price
        +int regular_price
        +string image_urls
        +string brand
        +bool is_available
        +bool is_best_seller
        +bool is_new_arrival
        +bool is_featured
        +bool is_special_offer
        +datetime created_at
        +datetime updated_at
    }

    class Category {
        +int id PK
        +string name
        +string slug UNIQUE
        +string description
        +bool is_mega
        +string image_url
        +datetime created_at
        +datetime updated_at
    }

    class Order {
        +int id PK
        +string client_name
        +int quantity
        +int taxe
        +int order_cost_ttc
        +int order_cost_ht
        +string status
        +bool is_paid
        +int carrier_id
        +int carrier_price
        +string carrier_name
        +string billing_address
        +string shipping_address
        +string payment_method
        +string stripe_client_secret
        +string paypal_client_secret
        +datetime created_at
        +datetime updated_at
        +int user_id FK
    }

    class OrderDetails {
        +int id PK
        +string product_name
        +string product_description
        +int product_solde_price
        +int product_regular_price
        +int quantity
        +int taxe
        +int subtotal
        +datetime created_at
        +datetime updated_at
        +int my_order_id FK
    }

    class Carrier {
        +int id PK
        +string name
        +string description
        +int price
        +datetime created_at
        +datetime updated_at
    }

    class Comment {
        +int id PK
        +int rating
        +string content
        +string email
        +bool is_published
        +datetime created_at
        +int author_id FK
        +int product_id FK
    }

    class Contact {
        +int id PK
        +string subject
        +string email
        +string content
        +string response
        +datetime created_at
        +datetime responded_at
    }

    class ResetPasswordRequest {
        +int id PK
        +string selector
        +string hashed_token
        +datetime requested_at
        +datetime expires_at
        +int user_id FK
    }

    class PaymentMethod {
        +int id PK
        +string name
        +string description
        +string more_description
        +string image_url
        +string test_public_api_key
        +string test_private_api_key
        +string prod_public_api_key
        +string prod_private_api_key
        +string test_base_url
        +string prod_base_url
        +datetime created_at
        +datetime updated_at
    }

    class Collection {
        +int id PK
        +string title
        +string description
        +string button_text
        +string button_link
        +string image_url
        +bool is_mega
        +datetime created_at
        +datetime updated_at
    }

    class Page {
        +int id PK
        +string title
        +string slug UNIQUE
        +string content
        +bool is_head
        +bool is_foot
        +datetime created_at
        +datetime updated_at
    }

    class Setting {
        +int id PK
        +string website_name
        +string description
        +string currency
        +int taxe_rate
        +string logo
        +string street
        +string city
        +string code_postal
        +string state
        +string phone
        +string email
        +string facebook_link
        +string youtube_link
        +string instagram_link
        +string copyright
        +datetime created_at
        +datetime updated_at
    }

    class Sliders {
        +int id PK
        +string title
        +string description
        +string button_text
        +string button_link
        +string image_url
        +datetime created_at
        +datetime updated_at
    }

    %% ─────────────────────────────────────────────
    %% ASSOCIATIONS ET RELATIONS
    %% ─────────────────────────────────────────────

    %% User → Address : Composition
    %% Un User possède ses adresses. Si User supprimé → adresses supprimées
    User "1" *-- "0..*" Address : possède

    %% User → Order : Agrégation
    %% Un User peut avoir plusieurs commandes
    User "1" o-- "0..*" Order : passe

    %% User → Comment : Association
    %% Un User peut écrire plusieurs commentaires
    User "1" --> "0..*" Comment : rédige

    %% User → ResetPasswordRequest : Composition
    %% Le token reset est lié à l'utilisateur, supprimé après usage
    User "1" *-- "0..*" ResetPasswordRequest : demande

    %% Order → OrderDetails : Composition
    %% Les détails n'existent pas sans la commande
    Order "1" *-- "1..*" OrderDetails : contient

    %% Order → Carrier : Association (snapshot)
    %% Le transporteur est copié dans la commande (carrier_name, carrier_price)
    Order "0..*" --> "1" Carrier : utilise

    %% Product ↔ Category : Association ManyToMany
    %% Via table pivot product_category
    Product "0..*" --> "1..*" Category : appartient à

    %% Product ↔ Product : Auto-association ManyToMany
    %% Via table pivot product_related_products
    Product "0..*" --> "0..*" Product : produits liés

    %% Comment → Product : Association
    %% Un commentaire porte sur un produit
    Comment "0..*" --> "1" Product : concerne

    %% OrderDetails snapshot de Product
    %% (pas de FK directe, les données sont copiées au moment de la commande)
    OrderDetails ..> Product : snapshot données
```

---

## Légende des relations

| Notation Mermaid | Type UML | Signification dans C.G Boutique |
|---|---|---|
| `A "1" *-- "0..*" B` | **Composition** | B ne peut pas exister sans A. Si A est supprimé, B l'est aussi |
| `A "1" o-- "0..*" B` | **Agrégation** | B peut exister indépendamment de A |
| `A "1" --> "0..*" B` | **Association** | A utilise B, relation directe avec FK |
| `A ..> B` | **Dépendance** | A dépend de B sans FK directe (données copiées) |

---

## Détail des relations identifiées depuis le SQL

### Compositions (cycle de vie lié)

| Classe parent | Classe enfant | Raison |
|---|---|---|
| `User` | `Address` | `ON DELETE CASCADE` implicite — adresses appartiennent à l'user |
| `User` | `ResetPasswordRequest` | Token supprimé après usage ou si user supprimé |
| `Order` | `OrderDetails` | Les lignes de commande n'ont aucun sens sans la commande |

### Agrégations (cycle de vie indépendant)

| Classe parent | Classe enfant | Raison |
|---|---|---|
| `User` | `Order` | Une commande peut être conservée même si l'user est archivé |

### Associations simples (FK directe)

| Classe source | Classe cible | Cardinalité | FK dans SQL |
|---|---|---|---|
| `Order` | `Carrier` | N → 1 | `carrier_id` + snapshot (carrier_name, carrier_price) |
| `Comment` | `User` | N → 1 | `author_id` → `user.id` |
| `Comment` | `Product` | N → 1 | `product_id` → `product.id` |
| `Product` | `Category` | N ↔ N | Table pivot `product_category` |
| `Product` | `Product` | N ↔ N | Table pivot `product_related_products` |

### Classes indépendantes (pas de FK)

| Classe | Rôle dans le projet |
|---|---|
| `Contact` | Formulaire de contact client → réponse admin dans EasyAdmin |
| `Collection` | Bannières promotionnelles gérées via EasyAdmin |
| `Page` | Pages statiques (CGV, Mentions légales) gérées via EasyAdmin |
| `Setting` | Configuration globale du site (nom, logo, réseaux sociaux) |
| `Sliders` | Carrousel hero page d'accueil géré via EasyAdmin |
| `PaymentMethod` | Configuration Stripe/PayPal (clés API) gérée via EasyAdmin |

---

## Tables pivot (relations ManyToMany)

Ces tables n'apparaissent pas comme classes dans le diagramme car elles sont gérées automatiquement par Doctrine ORM :

```
product_category          → Product ↔ Category (ManyToMany)
product_related_products  → Product ↔ Product  (ManyToMany auto-référence)
```

---

## Notes Symfony / Doctrine

- **`Order::status`** → géré par un `ChoiceField` EasyAdmin : `pending | paid | shipped | delivered | cancelled`
- **`Order::is_paid`** → `BooleanField` EasyAdmin (tinyint MySQL → bool PHP)
- **`OrderDetails`** → snapshot du produit au moment de l'achat (product_name, product_solde_price copiés) — pas de FK vers Product intentionnellement pour conserver l'historique si le produit est modifié ou supprimé
- **`User::roles`** → JSON array en BDD (`["ROLE_USER"]`, `["ROLE_USER","ROLE_ADMIN"]`)
- **`ResetPasswordRequest`** → géré par le bundle `symfonycasts/reset-password-bundle`
- **`Product::image_urls`** → JSON stringifié contenant les URLs des images