<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303102138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE certification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, course_id INT NOT NULL, certificate_number VARCHAR(255) NOT NULL, obtained_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_6C3C6D753005EFE3 (certificate_number), INDEX IDX_6C3C6D75A76ED395 (user_id), INDEX IDX_6C3C6D75591CC992 (course_id), UNIQUE INDEX user_course_unique (user_id, course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, theme_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, price NUMERIC(10, 2) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_169E6FB959027487 (theme_id), INDEX IDX_169E6FB9B03A8386 (created_by_id), INDEX IDX_169E6FB9896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lesson (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL, video_url VARCHAR(255) DEFAULT NULL, price NUMERIC(10, 2) NOT NULL, position INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_F87474F3591CC992 (course_id), INDEX IDX_F87474F3B03A8386 (created_by_id), INDEX IDX_F87474F3896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lesson_validation (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, lesson_id INT NOT NULL, validated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_1BA512BFA76ED395 (user_id), INDEX IDX_1BA512BFCDF80196 (lesson_id), UNIQUE INDEX user_lesson_unique (user_id, lesson_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE purchase (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, course_id INT DEFAULT NULL, lesson_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, amount NUMERIC(10, 2) NOT NULL, stripe_payment_intent_id VARCHAR(255) NOT NULL, status VARCHAR(50) NOT NULL, purchased_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6117D13BA76ED395 (user_id), INDEX IDX_6117D13B591CC992 (course_id), INDEX IDX_6117D13BCDF80196 (lesson_id), INDEX IDX_6117D13BB03A8386 (created_by_id), INDEX IDX_6117D13B896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE theme (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9775E708B03A8386 (created_by_id), INDEX IDX_9775E708896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, is_verified TINYINT(1) NOT NULL, verification_token VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8D93D649B03A8386 (created_by_id), INDEX IDX_8D93D649896DBBDE (updated_by_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE certification ADD CONSTRAINT FK_6C3C6D75A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE certification ADD CONSTRAINT FK_6C3C6D75591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB959027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9B03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB9896DBBDE FOREIGN KEY (updated_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3B03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3896DBBDE FOREIGN KEY (updated_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE lesson_validation ADD CONSTRAINT FK_1BA512BFA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE lesson_validation ADD CONSTRAINT FK_1BA512BFCDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13BA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13BCDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13BB03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE purchase ADD CONSTRAINT FK_6117D13B896DBBDE FOREIGN KEY (updated_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE theme ADD CONSTRAINT FK_9775E708B03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE theme ADD CONSTRAINT FK_9775E708896DBBDE FOREIGN KEY (updated_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649B03A8386 FOREIGN KEY (created_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649896DBBDE FOREIGN KEY (updated_by_id) REFERENCES `user` (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certification DROP FOREIGN KEY FK_6C3C6D75A76ED395');
        $this->addSql('ALTER TABLE certification DROP FOREIGN KEY FK_6C3C6D75591CC992');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB959027487');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9B03A8386');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB9896DBBDE');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3591CC992');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3B03A8386');
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3896DBBDE');
        $this->addSql('ALTER TABLE lesson_validation DROP FOREIGN KEY FK_1BA512BFA76ED395');
        $this->addSql('ALTER TABLE lesson_validation DROP FOREIGN KEY FK_1BA512BFCDF80196');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13BA76ED395');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B591CC992');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13BCDF80196');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13BB03A8386');
        $this->addSql('ALTER TABLE purchase DROP FOREIGN KEY FK_6117D13B896DBBDE');
        $this->addSql('ALTER TABLE theme DROP FOREIGN KEY FK_9775E708B03A8386');
        $this->addSql('ALTER TABLE theme DROP FOREIGN KEY FK_9775E708896DBBDE');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649B03A8386');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649896DBBDE');
        $this->addSql('DROP TABLE certification');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE lesson');
        $this->addSql('DROP TABLE lesson_validation');
        $this->addSql('DROP TABLE purchase');
        $this->addSql('DROP TABLE theme');
        $this->addSql('DROP TABLE `user`');
    }
}
