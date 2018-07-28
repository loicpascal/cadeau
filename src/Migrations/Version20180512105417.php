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

        $this->addSql('ALTER TABLE idee ADD user_adding_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE idee ADD CONSTRAINT FK_DE60E5CB0A18E3C FOREIGN KEY (user_adding_id) REFERENCES app_user (id)');
        $this->addSql('CREATE INDEX IDX_DE60E5CB0A18E3C ON idee (user_adding_id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE idee DROP FOREIGN KEY FK_DE60E5CB0A18E3C');
        $this->addSql('DROP INDEX IDX_DE60E5CB0A18E3C ON idee');
        $this->addSql('ALTER TABLE idee DROP user_adding_id');
    }
}
