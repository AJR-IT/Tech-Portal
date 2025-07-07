<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250707020621 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD COLUMN default_starting_page VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD COLUMN last_logon_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD COLUMN lsat_logon_ip VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password, state_unique_id, local_unique_id, graduation_year, date_created, username, first_name, last_name, enabled, can_log_in FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, state_unique_id VARCHAR(255) DEFAULT NULL, local_unique_id VARCHAR(255) DEFAULT NULL, graduation_year INTEGER DEFAULT NULL, date_created DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , username VARCHAR(255) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, enabled BOOLEAN NOT NULL, can_log_in BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO user (id, email, roles, password, state_unique_id, local_unique_id, graduation_year, date_created, username, first_name, last_name, enabled, can_log_in) SELECT id, email, roles, password, state_unique_id, local_unique_id, graduation_year, date_created, username, first_name, last_name, enabled, can_log_in FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
    }
}
