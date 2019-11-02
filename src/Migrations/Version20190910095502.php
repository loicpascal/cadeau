<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190910095502 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE idee_team (idee_id INT NOT NULL, team_id INT NOT NULL, INDEX IDX_9C3851A2D40D782A (idee_id), INDEX IDX_9C3851A2296CD8AE (team_id), PRIMARY KEY(idee_id, team_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE idee_team ADD CONSTRAINT FK_9C3851A2D40D782A FOREIGN KEY (idee_id) REFERENCES idee (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE idee_team ADD CONSTRAINT FK_9C3851A2296CD8AE FOREIGN KEY (team_id) REFERENCES team (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE idee_team');
    }
}
