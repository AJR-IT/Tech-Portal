<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250705132519 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket_history ADD COLUMN message VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__ticket_history AS SELECT id, ticket_id, related_user_id, date_created FROM ticket_history');
        $this->addSql('DROP TABLE ticket_history');
        $this->addSql('CREATE TABLE ticket_history (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, ticket_id INTEGER NOT NULL, related_user_id INTEGER NOT NULL, date_created DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_2B762919700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2B76291998771930 FOREIGN KEY (related_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO ticket_history (id, ticket_id, related_user_id, date_created) SELECT id, ticket_id, related_user_id, date_created FROM __temp__ticket_history');
        $this->addSql('DROP TABLE __temp__ticket_history');
        $this->addSql('CREATE INDEX IDX_2B762919700047D2 ON ticket_history (ticket_id)');
        $this->addSql('CREATE INDEX IDX_2B76291998771930 ON ticket_history (related_user_id)');
    }
}
