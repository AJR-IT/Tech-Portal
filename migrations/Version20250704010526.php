<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250704010526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE config (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, config_key VARCHAR(255) NOT NULL, config_value VARCHAR(255) NOT NULL, default_value VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL)');
        $this->addSql('ALTER TABLE user ADD COLUMN first_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD COLUMN last_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD COLUMN enabled BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE user ADD COLUMN can_log_in BOOLEAN NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE config');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password, state_unique_id, local_unique_id, graduation_year, date_created, username FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, state_unique_id VARCHAR(255) DEFAULT NULL, local_unique_id VARCHAR(255) DEFAULT NULL, graduation_year INTEGER DEFAULT NULL, date_created DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , username VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO user (id, email, roles, password, state_unique_id, local_unique_id, graduation_year, date_created, username) SELECT id, email, roles, password, state_unique_id, local_unique_id, graduation_year, date_created, username FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
    }
}
