# 🎓 Knowledge Learning

Plateforme e-learning développée avec **Symfony 7. 4**, permettant aux utilisateurs d'acheter et de suivre des formations en ligne.

## 📋 Table des matières

- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Lancement du projet](#lancement-du-projet)
- [Tests](#tests)
- [Architecture](#architecture)
- [Fonctionnalités](#fonctionnalités)
- [Technologies utilisées](#technologies-utilisées)

---

## 🛠️ Prérequis

- **PHP 8.2** ou supérieur
- **Composer** 2.x
- **Node.js** 18.x et **Yarn**
- **MySQL/MariaDB** (via XAMPP ou autre)
- **Symfony CLI** (optionnel mais recommandé)
- **Stripe Account** (mode test pour les paiements)

---

## 📥 Installation

### 1. Cloner le projet

```bash
git clone https://github.com/MathP-dev/knowledge_learning.git
cd knowledge-learning
```

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Installer les dépendances JavaScript

```bash
yarn install
```

### 4. Configurer les variables d'environnement

Copier le fichier `.env` et le renommer en `.env.local` :

```bash
cp .env .env.local
```

Modifier `.env.local` avec vos paramètres :

```bash
# Base de données (XAMPP par défaut)
DATABASE_URL="mysql://root:@127.0.0.1:3306/knowledge_learning?serverVersion=mariadb-10.11.2&charset=utf8mb4"

# Mailer (utiliser mailpit ou mailtrap pour le développement)
MAILER_DSN=smtp://localhost:1025

# Stripe (clés de test)
STRIPE_PUBLIC_KEY=pk_test_votre_cle_publique
STRIPE_SECRET_KEY=sk_test_votre_cle_secrete
STRIPE_WEBHOOK_SECRET=whsec_votre_webhook_secret

# URL du site
SITE_BASE_URL=http://localhost:8000
```

### 5. Créer la base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 6. Charger les données de test (fixtures)

```bash
php bin/console doctrine:fixtures:load
```

**Comptes créés par défaut :**
- **Admin** : `admin@knowledge-learning.com` / `Admin123!`
- **Utilisateur** : `jean.dupont@example.com` / `User123!`

---

## ⚙️ Configuration

### Configuration Stripe

1. Créer un compte sur [Stripe](https://stripe.com)
2. Récupérer vos **clés de test** dans le dashboard
3. Les ajouter dans `.env.local`

#### Clé whsec_ pour les webhooks Stripe en local :
1. Installer Stripe CLI ``scoop install stripe``
2. Se connecter avec ``stripe login``
3. Lancer l'écoute des webhooks : ``stripe listen --forward-to https://localhost:8000/webhook/stripe``
4. Sortie attendue : ``> Ready! Your webhook signing secret is whsec_...``

### Configuration Email

Pour tester l'envoi d'emails en local, installer **Mailpit** :

```bash
# Avec Docker
docker run -d -p 1025:1025 -p 8025:8025 axllent/mailpit

# Accéder à l'interface :  http://localhost:8025
```

---

## 🚀 Lancement du projet

### 1. Compiler les assets

```bash
# Mode développement (watch)
yarn watch

# OU mode production
yarn build
```

### 2. Démarrer le serveur Symfony

```bash
# Avec Symfony CLI (recommandé)
symfony serve

# OU avec PHP
php -S localhost:8000 -t public/
```

### 3. Accéder à l'application

Ouvrir votre navigateur :  **http://localhost:8000**

---

## 🧪 Tests

### Consulter le fichier CONFIG_TEST.md et TESTS_ANALYSIS.md

# Lancer les tests
php bin/phpunit

---

## 🏗️ Architecture

### Structure des dossiers

```
knowledge-learning/
├── config/              # Configuration Symfony
├── migrations/          # Migrations de base de données
├── public/              # Point d'entrée public (index.php)
├── src/
│   ├── Controller/      # Action Controllers (1 classe = 1 action)
│   ├── Entity/          # Entités Doctrine
│   ├── Repository/      # Repositories Doctrine
│   ├── Service/         # Logique métier (Services)
│   ├── DTO/             # Data Transfer Objects
│   ├── Security/        # Authenticator personnalisé
│   ├── EventListener/   # Event Listeners
│   └── DataFixtures/    # Données de test
├── templates/           # Templates Twig
├── tests/               # Tests unitaires et fonctionnels
│   ├── Unit/
│   └── Functional/
├── assets/              # Assets JS/CSS
│   ├── js/
│   └── styles/
└── var/                 # Cache et logs
```

### Design Patterns utilisés

- **Action Controller Pattern** : Un contrôleur = une méthode `__invoke()`
- **Service Layer Pattern** :  Logique métier dans des services dédiés
- **Repository Pattern** : Accès aux données via Doctrine
- **DTO Pattern** : Validation et transfert de données
- **Strategy Pattern** : Gestion des paiements avec Stripe

---

## ✨ Fonctionnalités

### 🔐 Authentification

- ✅ Inscription utilisateur avec validation email
- ✅ Connexion / Déconnexion
- ✅ Vérification du compte par email
- ✅ Authenticator personnalisé Symfony
- ✅ Restriction d'accès selon les rôles (ROLE_USER, ROLE_ADMIN)

### 📚 Gestion des formations

- ✅ Affichage des thèmes, cursus et leçons
- ✅ Système de navigation hiérarchique
- ✅ Accès aux leçons selon les achats
- ✅ Contenu Lorem Ipsum pour les leçons

### 💳 E-commerce

- ✅ Achat de cursus complets
- ✅ Achat de leçons individuelles
- ✅ Intégration Stripe Checkout (mode test)
- ✅ Gestion des paiements et des transactions
- ✅ Historique des achats utilisateur

### 🏆 Certifications

- ✅ Validation des leçons par l'utilisateur
- ✅ Attribution automatique de certifications
- ✅ Certification obtenue après validation de toutes les leçons d'un thème
- ✅ Page récapitulative des certifications

### 👨‍💼 Administration

- ✅ Dashboard administrateur
- ✅ Gestion des utilisateurs
- ✅ Vue d'ensemble des cursus
- ✅ Suivi des achats
- ✅ CRUD avec EasyAdminBundle

---

## 🛠️ Technologies utilisées

### Backend

- **Symfony 7.4** 
- **Doctrine ORM** (Gestion base de données)
- **MySQL / MariaDB** (Base de données)
- **Twig** (Moteur de templates)
- **PHPUnit** (Tests unitaires)

### Frontend

- **Bootstrap 5.3** (Framework CSS)
- **Vanilla JavaScript** (Interactions)
- **Webpack Encore** (Bundler assets)

### Paiement

- **Stripe** (Paiements sécurisés)

### Outils

- **Composer** (Gestionnaire de dépendances PHP)
- **Yarn** (Gestionnaire de dépendances JS)
- **Symfony CLI** (Outil de développement)
- **XAMPP** (Environnement local)

---

## 📊 Schéma de Base de Données

```
Theme
  ├── id
  ├── name
  ├── slug
  └── description

Course
  ├── id
  ├── theme_id (FK)
  ├── title
  ├── slug
  ├── description
  ├── price
  └── created_at

Lesson
  ├── id
  ├── course_id (FK)
  ├── title
  ├── slug
  ├── content
  ├── video_url
  ├── price
  ├── position
  └── created_at

User
  ├── id
  ├── email
  ├── password
  ├── first_name
  ├── last_name
  ├── roles
  ├── is_verified
  ├── verification_token
  └── created_at

Purchase
  ├── id
  ├── user_id (FK)
  ├── course_id (FK) [nullable]
  ├── lesson_id (FK) [nullable]
  ├── amount
  ├── stripe_payment_intent_id
  ├── status
  └── purchased_at

LessonValidation
  ├── id
  ├── user_id (FK)
  ├── lesson_id (FK)
  └── validated_at

Certification
  ├── id
  ├── user_id (FK)
  ├── theme_id (FK)
  └── obtained_at
```
---


---

## 🐛 Résolution de problèmes

### Erreur de connexion à la base de données

Vérifier que XAMPP est lancé et que MySQL tourne sur le port 3306.

### Les assets ne se chargent pas

```bash
yarn build
php bin/console cache:clear
```

### Erreur Stripe

Vérifier que les clés Stripe sont bien configurées dans `.env.local`.

### Les emails ne partent pas

Vérifier que Mailpit est lancé sur le port 1025 :

```bash
docker ps
```
