# 🎓 Knowledge Learning

Plateforme e-learning développée avec **Symfony 7.4**, permettant aux utilisateurs d'acheter et de suivre des formations en ligne.

## 📋 Table des matières

- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Sécurité](#sécurité)
- [Lancement du projet](#lancement-du-projet)
- [Tests](#tests)
- [Architecture](#architecture)
- [Fonctionnalités](#fonctionnalités)
- [Technologies utilisées](#technologies-utilisées)
- [Résolution de problèmes](#résolution-de-problèmes)

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

### 4. Créer l'utilisateur MySQL dédié

**Pour des raisons de sécurité**, l'application utilise un compte MySQL dédié (pas `root`).

```bash
# Créer l'utilisateur avec les privilèges appropriés
mysql -u root -p < database/setup_user.sql
```

> 📖 **Plus de détails** : [Section Sécurité](#sécurité) | [database/README.md](database/README.md)

### 5. Configurer les variables d'environnement

Copier le fichier d'exemple :

```bash
cp .env.local.example .env.local
```

Modifier `.env.local` avec vos paramètres :

```bash
# Base de données - Compte dédié (mot de passe par défaut : Kn0wl3dg3_S3cur3!)
DATABASE_URL="mysql://kl_app_user:VOTRE_MOT_DE_PASSE@127.0.0.1:3306/knowledge_learning?serverVersion=mariadb-10.11.2&charset=utf8mb4"

# Mailer (utiliser mailpit ou mailtrap pour le développement)
MAILER_DSN=smtp://localhost:1025

# Stripe (clés de test)
STRIPE_PUBLIC_KEY=pk_test_votre_cle_publique
STRIPE_SECRET_KEY=sk_test_votre_cle_secrete
STRIPE_WEBHOOK_SECRET=whsec_votre_webhook_secret

# URL du site
SITE_BASE_URL=http://localhost:8000
```

> ℹ️ Voir `.env.local.example` pour la configuration complète et les explications détaillées.

### 6. Créer la base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

> ℹ️ Ces commandes utilisent l'utilisateur `kl_app_user` configuré dans `.env.local`

### 7. Charger les données de test (fixtures)

```bash
php bin/console doctrine:fixtures:load
```

**Comptes créés par défaut :**
- **Admin** : `admin@knowledge-learning.com` / `Admin123!`
- **Utilisateur** : `jean.dupont@example.com` / `User123!`

---

## 🔒 Sécurité

### Configuration de la base de données

**L'application utilise un compte MySQL dédié** (pas `root`) pour respecter le principe de sécurité **"moindre privilège"**.
#### Privilèges accordés (développement)

**Compte** : `kl_app_user`

**Privilèges** :
- ✅ `CREATE` (global) : Permet de créer la base avec `doctrine:database:create`
- ✅ `SELECT, INSERT, UPDATE, DELETE` : Opérations CRUD
- ✅ `CREATE, ALTER, DROP` (sur `knowledge_learning.*`) : Migrations Doctrine
- ✅ `INDEX, REFERENCES` : Gestion des index et clés étrangères

**Privilèges refusés** :
- ❌ Création d'utilisateurs MySQL (`CREATE USER`, `GRANT`)
- ❌ Accès aux autres bases de données du serveur
- ❌ Commandes d'administration système (`SHUTDOWN`, `RELOAD`)

> 📝 **Note production** : En production, le privilège `CREATE` global serait retiré. Les bases sont créées manuellement par l'administrateur système.

#### Processus d'installation

L'ordre des étapes est important :

```bash
# 1. Créer l'utilisateur MySQL dédié (AVANT de l'utiliser)
mysql -u root -p < database/setup_user.sql

# 2. Configurer la connexion dans .env.local
cp .env.local.example .env.local
# Modifier DATABASE_URL avec : mysql://kl_app_user:MOT_DE_PASSE@...

# 3. Créer la base (utilise kl_app_user)
php bin/console doctrine:database:create

# 4. Appliquer les migrations
php bin/console doctrine:migrations:migrate

# 5. Vérifier la configuration
php bin/console doctrine:schema:validate
```

#### Documentation complète

Pour plus de détails sur la configuration de sécurité SQL :

📖 **[database/README.md](database/README.md)** - Documentation technique détaillée

**Contenu** :
- Script SQL complet avec commentaires
- Explications des privilèges
- Configuration production vs développement
- Dépannage et vérifications

---

## ⚙️ Configuration

### Configuration Stripe

1. Créer un compte sur [Stripe](https://stripe.com)
2. Récupérer vos **clés de test** dans le dashboard
3. Les ajouter dans `.env.local`

#### Clé whsec_ pour les webhooks Stripe en local :

1. Installer Stripe CLI `scoop install stripe`
2. Se connecter avec `stripe login`
3. Lancer l'écoute des webhooks : `stripe listen --forward-to http://localhost:8000/webhook/stripe`
4. Sortie attendue : `> Ready! Your webhook signing secret is whsec_...`

### Configuration Email

Pour tester l'envoi d'emails en local, installer **Mailpit** :

```bash
# Avec Docker
docker run -d -p 1025:1025 -p 8025:8025 axllent/mailpit

# Accéder à l'interface : http://localhost:8025
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

Ouvrir votre navigateur : **http://localhost:8000**

---

## 🧪 Tests

### Consulter le fichier CONFIG_TEST.md et TESTS_ANALYSIS.md

```bash
# Lancer les tests
php bin/phpunit
```

---

## 🏗️ Architecture

### Structure des dossiers

```
knowledge-learning/
├── config/              # Configuration Symfony
├── database/            # Scripts SQL et documentation
│   ├── setup_user.sql   # Création utilisateur MySQL dédié
│   └── README.md        # Documentation sécurité SQL
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
- **Service Layer Pattern** : Logique métier dans des services dédiés
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
  ├── course_id (FK)
  ├── certificate_number
  └── obtained_at
```

---

## 🐛 Résolution de problèmes

### Erreur de connexion à la base de données

**Erreur** : `Access denied for user 'kl_app_user'@'localhost'`

**Solutions** :
1. Vérifier que l'utilisateur `kl_app_user` a bien été créé :
   ```bash
   mysql -u root -p -e "SELECT User, Host FROM mysql.user WHERE User = 'kl_app_user';"
   ```

2. Vérifier le mot de passe dans `.env.local` (par défaut : `Kn0wl3dg3_S3cur3!`)

3. Réexécuter le script de création :
   ```bash
   mysql -u root -p < database/setup_user.sql
   ```

4. Vérifier que XAMPP est lancé et que MySQL tourne sur le port 3306

### Erreur : "Unknown database 'knowledge_learning'"

La base n'a pas été créée. Exécuter :

```bash
php bin/console doctrine:database:create
```

Si l'erreur persiste, créer la base manuellement avec root :

```bash
mysql -u root -p -e "CREATE DATABASE knowledge_learning CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Les assets ne se chargent pas

```bash
yarn build
php bin/console cache:clear
```

### Erreur Stripe

Vérifier que les clés Stripe sont bien configurées dans `.env.local`.

Les clés doivent commencer par :
- `pk_test_...` pour la clé publique
- `sk_test_...` pour la clé secrète

### Les emails ne partent pas

Vérifier que Mailpit est lancé sur le port 1025 :

```bash
docker ps
```

Ou accéder à l'interface : http://localhost:8025

### Erreur lors des migrations

```bash
# Réinitialiser complètement la base (ATTENTION : supprime toutes les données)
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

---

## 📝 Fichiers de configuration

- `.env` : Configuration par défaut (versionné)
- `.env.local` : Configuration locale (NON versionné, à créer)
- `.env.local.example` : Template de configuration (versionné)
- `database/setup_user.sql` : Script de création de l'utilisateur MySQL
- `database/README.md` : Documentation sécurité SQL

---

## 📚 Documentation complémentaire

- **Sécurité SQL** : [database/README.md](database/README.md)
- **Tests** : CONFIG_TEST.md et TESTS_ANALYSIS.md
- **Symfony** : https://symfony.com/doc/current/index.html
- **Doctrine** : https://www.doctrine-project.org/
- **Stripe** : https://stripe.com/docs

---

