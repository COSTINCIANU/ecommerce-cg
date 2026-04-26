<div align="center">
<picture>
  <source media="(prefers-color-scheme: dark)" srcset="docs/Logo_C_G_Blanc.png">
  <source media="(prefers-color-scheme: light)" srcset="docs/Logo_C_G_Normal_Noir.png">
  <img alt="Logo C.G Boutique" src="docs/Logo_C_G_Normal_Noir.png">
</picture>

# C.G Boutique — Application E-commerce

**Application e-commerce complète développée avec Symfony 7**

[![Tests PHPUnit](https://github.com/COSTINCIANU/ecommerce-cg/actions/workflows/tests.yml/badge.svg)](https://github.com/COSTINCIANU/ecommerce-cg/actions)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?logo=php)
![Symfony](https://img.shields.io/badge/Symfony-7.x-000000?logo=symfony)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql)
![Docker](https://img.shields.io/badge/Docker-Staging-2496ED?logo=docker)

*Projet de fin d'études — Gheorghina Costincianu | 2026*

</div>

---

## Présentation

C.G Boutique est une application e-commerce complète permettant la vente en ligne de vêtements et accessoires. Elle intègre un système de paiement sécurisé, un espace client complet, et un panel d'administration avancé.

---

## Fonctionnalités

### Côté client
- **Catalogue produits** — navigation par catégories, recherche, filtres
- **Fiche produit** — galerie d'images, avis clients, produits similaires
- **Panier d'achat** — ajout, modification, suppression en temps réel
- **Paiement sécurisé** — intégration Stripe (carte bancaire) et PayPal (sandbox)
- **Inscription / Connexion** — authentification sécurisée avec remember me
- **Réinitialisation de mot de passe** — envoi d'email via Mailjet
- **Espace compte** — historique des commandes, adresses, profil
- **Liste de souhaits** — sauvegarde des produits favoris
- **Comparaison de produits** — comparaison jusqu'à plusieurs produits
- **Avis et commentaires** — notation et commentaires sur les produits
- **Transporteurs** — choix du mode de livraison avec calcul des frais
- **Factures** — génération et téléchargement des factures

### Côté administration (EasyAdmin)
- Gestion des produits, catégories, stocks
- Gestion des commandes et statuts
- Gestion des utilisateurs
- Gestion des transporteurs
- Configuration des moyens de paiement (clés Stripe / PayPal via interface)
- Gestion des avis clients

---

## Stack technique

| Technologie | Version | Rôle |
|-------------|---------|------|
| PHP | 8.2 | Langage backend |
| Symfony | 7.x | Framework PHP |
| Doctrine ORM | 3.x | Gestion base de données |
| MySQL | 8.0 | Base de données |
| Twig | 3.x | Moteur de templates |
| Asset Mapper | — | Gestion des assets CSS/JS |
| EasyAdmin | 5.x | Panel d'administration |
| Stripe | — | Paiement par carte bancaire |
| PayPal SDK | — | Paiement PayPal |
| Mailjet | — | Envoi d'emails transactionnels |
| PHPUnit | 11.x | Tests unitaires et fonctionnels |
| Cypress | — | Tests E2E |
| Docker | — | Environnement staging |
| GitHub Actions | — | CI/CD Pipeline |

---
## 📊 Documentation UML
| Diagramme | Lien |
|---|---|
| Diagrammes d'Activité (Mermaid) | [Voir →](Mermaid/cg-diagrammes-activite-mermaid.md) |
<!-- | Diagrammes de Séquences | [Voir →](mermaid/) | -->


## Installation locale

### Prérequis
- PHP 8.2+
- Composer
- MySQL 8.0
- Symfony CLI
- Node.js + npm

### Étapes

```bash
# 1. Cloner le projet
git clone https://github.com/COSTINCIANU/ecommerce-cg.git
cd ecommerce-cg

# 2. Installer les dépendances PHP
composer install

# 3. Copier et configurer les variables d'environnement
cp .env.example .env.local
# Éditer .env.local avec vos paramètres (BDD, Mailjet, etc.)

# 4. Créer la base de données et lancer les migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# 5. Charger les données de test
php bin/console doctrine:fixtures:load

# 6. Compiler les assets
php bin/console asset-map:compile

# 7. Lancer le serveur
symfony serve
```

L'application est accessible sur `http://localhost:8000`


## ⚙️ Configuration Docker

Les fichiers `docker-compose.yml` et `docker-compose.staging.yml` ne sont pas 
committés pour des raisons de sécurité.

### 🚀 Pour démarrer le projet :

1. Copie les fichiers exemple :
\```bash
cp docker-compose.example.yml docker-compose.yml
cp docker-compose.staging.example.yml docker-compose.staging.yml
\```

2. Crée ton fichier de variables :
\```bash
cp .env.docker.example .env.docker
\```

3. Remplis tes vraies valeurs dans `.env.docker`

4. Lance Docker :
\```bash
docker compose --env-file .env.docker up -d
\```



## Tests

### Tests unitaires et fonctionnels (PHPUnit)

```bash
# Tests unitaires (74 tests)
php bin/phpunit tests/Unit/ --testdox

# Tests fonctionnels (29 tests)
php bin/phpunit tests/Functional/ --testdox

# Tous les tests
php bin/phpunit --testdox
```

**Résultats** : 103 tests — 164 assertions — 0 échec ✅

### Tests E2E (Cypress)

```bash
npx cypress open
```

### Tests de sécurité (OWASP ZAP)

Les tests de sécurité ont été réalisés avec OWASP ZAP sur l'environnement staging.
Le rapport complet est disponible dans `docs/zap-report.pdf`.

**Résultats** : 0 faille critique (High) — résultat satisfaisant ✅

---

## CI/CD

Le pipeline GitHub Actions se déclenche automatiquement à chaque push sur `master` :

1. Configuration PHP 8.2
2. Installation des dépendances Composer
3. Création de la base de données de test
4. Exécution des migrations
5. Chargement des fixtures
6. Lancement des tests unitaires
7. Lancement des tests fonctionnels

---

## Sécurité

- Protection CSRF sur tous les formulaires Symfony
- Headers de sécurité Nginx configurés (CSP, X-Frame-Options, X-Content-Type-Options)
- Mots de passe hashés avec bcrypt
- Tokens de réinitialisation de mot de passe avec expiration
- Authentification sécurisée avec Symfony Security
- Tests OWASP ZAP réalisés sur environnement staging isolé

---

## Documentation

| Document | Description |
|----------|-------------|
| `docs/rapport-tests-phpunit.pdf` | Rapport complet des tests PHPUnit |
| `docs/zap-report.pdf` | Rapport de sécurité OWASP ZAP |
| `docs/lighthouse-report.pdf` | Rapport de performance Lighthouse |

---

## Auteure

**Gheorghina Costincianu**
Projet de fin d'études — 2026

---

<div align="center">
  <i>C.G Boutique — Tous droits réservés © 2026</i>
</div>