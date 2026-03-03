-- ====================================================
-- Configuration utilisateur MySQL pour Knowledge Learning
-- ====================================================

-- Création de l'utilisateur
CREATE USER IF NOT EXISTS 'kl_app_user'@'localhost' IDENTIFIED BY 'Kn0wl3dg3_S3cur3!';

-- Privilège de création de bases
GRANT CREATE ON *.* TO 'kl_app_user'@'localhost';

-- Privilèges complets sur la base principale
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, INDEX, ALTER, DROP, REFERENCES
      ON knowledge_learning.*
          TO 'kl_app_user'@'localhost';

-- Privilèges sur la base de test
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, INDEX, ALTER, DROP, REFERENCES
      ON knowledge_learning_test.*
          TO 'kl_app_user'@'localhost';

-- Support des tests parallèles
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, INDEX, ALTER, DROP, REFERENCES
      ON `knowledge_learning_test%`.*
          TO 'kl_app_user'@'localhost';

-- Application
FLUSH PRIVILEGES;

-- Vérification
SHOW GRANTS FOR 'kl_app_user'@'localhost';

SELECT 'Utilisateur créé avec succès !' AS Status;
