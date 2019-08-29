<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180512105417 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE IF NOT EXISTS `app_user` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
            `password` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
            `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `is_active` tinyint(1) NOT NULL,
            `lastname` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
            `firstname` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
            `birthdate` date DEFAULT NULL,
            `role` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
            PRIMARY KEY (`id`)
        )');
        $this->addSql('CREATE TABLE IF NOT EXISTS `comment` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `idee_id` int(11) NOT NULL,
            `user_id` int(11) NOT NULL,
            `date` datetime NOT NULL,
            `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
            PRIMARY KEY (`id`),
            KEY `IDX_9474526CD40D782A` (`idee_id`),
            KEY `IDX_9474526CA76ED395` (`user_id`)
        )');
        $this->addSql('CREATE TABLE IF NOT EXISTS `idee` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `libelle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
            `commentaire` longtext COLLATE utf8mb4_unicode_ci,
            `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `state` int(11) NOT NULL,
            `user_taking_id` int(11) DEFAULT NULL,
            `user_adding_id` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `IDX_DE60E5CA76ED395` (`user_id`),
            KEY `IDX_DE60E5CE85EF78F` (`user_taking_id`),
            KEY `IDX_DE60E5CB0A18E3C` (`user_adding_id`)
        )');
        $this->addSql('ALTER TABLE `comment`
            ADD CONSTRAINT `FK_9474526CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `app_user` (`id`),
            ADD CONSTRAINT `FK_9474526CD40D782A` FOREIGN KEY (`idee_id`) REFERENCES `idee` (`id`);'
        );
        $this->addSql('ALTER TABLE `idee`
            ADD CONSTRAINT `FK_DE60E5CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `app_user` (`id`),
            ADD CONSTRAINT `FK_DE60E5CB0A18E3C` FOREIGN KEY (`user_adding_id`) REFERENCES `app_user` (`id`),
            ADD CONSTRAINT `FK_DE60E5CE85EF78F` FOREIGN KEY (`user_taking_id`) REFERENCES `app_user` (`id`);'
        );
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

    }
}
