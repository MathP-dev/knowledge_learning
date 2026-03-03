## 🔒 Configuration de la sécurité de la base de données

### 1. Créer l'utilisateur MySQL dédié

**Important** : L'application n'utilise PAS le compte `root` pour des raisons de sécurité.

Exécuter le script de création d'utilisateur :

```bash
# Avec les droits administrateur MySQL (root)
mysql -u root -p < database/setup_user.sql
```

Ou via PhpMyAdmin : Importer le fichier `database/setup_user.sql`

**Utilisateur créé :**
- Nom : `kl_app_user`
- Hôte : `localhost`
- Mot de passe : Défini dans le script (à modifier selon votre politique de sécurité)

### 2. Configurer les identifiants dans Symfony

Copier le fichier d'exemple :

```bash
cp .env.local.example .env.local
```

Modifier `.env.local` avec vos vraies valeurs :

```bash
# Remplacer VOTRE_MOT_DE_PASSE par le mot de passe défini dans setup_user.sql
DATABASE_URL="mysql://kl_app_user:VOTRE_MOT_DE_PASSE@127.0.0.1:3306/knowledge_learning?..."
```


### 3. Vérifier la configuration

```bash
php bin/console doctrine:schema:validate
```

Si la connexion réussit, la configuration est correcte ✅
