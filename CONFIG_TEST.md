# 🧪 Configuration des Tests - Knowledge Learning


---

## 📋 Vue d'ensemble

- Configuration de la base de données de test
- Configuration PHPUnit
- Variables d'environnement
- Lancement des tests

---

## 🗄️ 1. Configuration de la Base de Données de Test


### Étape 1.1 : Configurer `.env.test`

Ajoutez la configuration suivante dans `.env.test` :

```dotenv
###> symfony/framework-bundle ###
APP_ENV=test
KERNEL_CLASS='App\Kernel'
APP_SECRET='$ecretf0rt3st'
###< symfony/framework-bundle ###

###> doctrine/doctrine-bundle ###
# Base de données de test MySQL
DATABASE_URL="mysql://root:@127.0.0.1:3306/knowledge_test?serverVersion=8.0&charset=utf8mb4"
###< doctrine/doctrine-bundle ###

###> stripe/stripe-php ###
# Clés Stripe factices pour les tests (pas de vraies requêtes API)
STRIPE_PUBLIC_KEY=pk_test_fake_key_for_testing
STRIPE_SECRET_KEY=sk_test_fake_key_for_testing
STRIPE_WEBHOOK_SECRET=whsec_fake_secret_for_testing
###< stripe/stripe-php ###
```

**Notes importantes :**
- Le nom de la BDD est `knowledge_test` (Symfony ajoute automatiquement `_test` dans certains contextes)
- Les clés Stripe sont factices pour les tests unitaires (pas d'appels API réels)
- Adaptez `root:@` avec vos identifiants MySQL si nécessaire

### Étape 1.2 : Créer la base de données de test

```bash
# Créer la base de données
php bin/console doctrine:database:create --env=test

# Si la BDD existe déjà, la supprimer puis recréer
php bin/console doctrine:database:drop --env=test --force --if-exists
php bin/console doctrine:database:create --env=test
```

### Étape 1.3 : Jouer les migrations

```bash
# Créer le schéma de la base de données
php bin/console doctrine:migrations:migrate --env=test --no-interaction

# OU mettre à jour le schéma directement
php bin/console doctrine:schema:update --env=test --force
```

### Étape 1.4 : Vérifier la création

```bash
# Vérifier que toutes les tables sont créées
php bin/console doctrine:schema:validate --env=test
```

**Résultat attendu :**
```
[Mapping]  OK - The mapping files are correct.
[Database] OK - The database schema is in sync with the mapping files.
```

---

## ⚙️ 2. Configuration de PHPUnit

### Étape 2.1 : Fichier `phpunit.xml`

Le fichier `phpunit.xml` doit être configuré pour PHPUnit 9 :

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         colors="true"
         bootstrap="tests/bootstrap.php"
>
    <php>
        <ini name="display_errors" value="1" />
        <ini name="error_reporting" value="-1" />
        <server name="APP_ENV" value="test" force="true" />
        <server name="SHELL_VERBOSITY" value="-1" />
        <server name="KERNEL_CLASS" value="App\Kernel" />
    </php>

    <testsuites>
        <testsuite name="Project Test Suite">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

**Points importants :**
- `KERNEL_CLASS` est **obligatoire** pour les tests fonctionnels
- `APP_ENV=test` force l'environnement de test

### Étape 2.2 : Vérifier la version de PHPUnit

```bash
php bin/phpunit --version
```

**Résultat attendu :**
```
PHPUnit 9.6.31 by Sebastian Bergmann and contributors.
```

---

## 🏗️ 3. Structure des Tests

### Types de tests

#### Tests Unitaires (Unit/)
- **N'utilisent PAS** la base de données
- Utilisent des **mocks** pour les dépendances
- **Rapides** et **isolés**
- Testent la logique métier pure

#### Tests Fonctionnels (Functional/)
- **Utilisent** la base de données de test
- Testent des **scénarios complets**
- Utilisent `WebTestCase` de Symfony

#### Tests Repositories (Unit/Repository/)
- **Utilisent** la base de données de test
- Testent les requêtes Doctrine
- Utilisent `KernelTestCase`

---

## 🚀 4. Lancement des Tests

### Commandes de base

#### Tous les tests
```bash
php bin/phpunit
```

#### Avec format lisible (testdox)
```bash
php bin/phpunit --testdox
```


## 📝 8. Checklist de Configuration

Avant de lancer les tests, vérifiez :

- [x] `.env.test` créé et configuré
- [x] Base de données `knowledge_test` créée
- [x] Migrations jouées sur la BDD de test
- [x] `phpunit.xml` correctement configuré avec `KERNEL_CLASS`
- [x] Clés Stripe configurées (même factices)
- [x] Tests utilisent `uniqid()` pour les emails
- [x] Cache PHPUnit vidé si nécessaire

---

## 📚 10. Ressources

### Documentation officielle
- [PHPUnit Documentation](https://phpunit.readthedocs.io/)
- [Symfony Testing](https://symfony.com/doc/current/testing.html)
- [Doctrine Testing](https://www.doctrine-project.org/projects/doctrine-orm/en/current/reference/testing.html)

### Fichiers de documentation du projet
- `TESTS_ANALYSIS.md` - Analyse complète des tests
---


