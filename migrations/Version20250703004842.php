<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250703004842 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, created_by_user_id INTEGER DEFAULT NULL, ticket_id INTEGER DEFAULT NULL, user_id INTEGER DEFAULT NULL, date_created DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , date_modified DATETIME DEFAULT NULL, subject VARCHAR(255) DEFAULT NULL, comment VARCHAR(255) NOT NULL, CONSTRAINT FK_9474526C7D182D95 FOREIGN KEY (created_by_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9474526C700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_9474526C7D182D95 ON comment (created_by_user_id)');
        $this->addSql('CREATE INDEX IDX_9474526C700047D2 ON comment (ticket_id)');
        $this->addSql('CREATE INDEX IDX_9474526CA76ED395 ON comment (user_id)');
        $this->addSql('CREATE TABLE location (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, parent_id INTEGER DEFAULT NULL, friendly_name VARCHAR(255) DEFAULT NULL, full_name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_5E9E89CB727ACA70 FOREIGN KEY (parent_id) REFERENCES location (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_5E9E89CB727ACA70 ON location (parent_id)');
        $this->addSql('CREATE TABLE status (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, friendly_name VARCHAR(255) DEFAULT NULL, full_name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, deletable BOOLEAN NOT NULL)');
        $this->addSql('CREATE TABLE tag (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, friendly_name VARCHAR(255) DEFAULT NULL, full_name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, deletable BOOLEAN NOT NULL)');
        $this->addSql('CREATE TABLE ticket (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, requesting_user_id INTEGER DEFAULT NULL, assigned_user_id INTEGER DEFAULT NULL, assigned_group_id INTEGER DEFAULT NULL, resolved_by_id INTEGER DEFAULT NULL, closed_by_id INTEGER DEFAULT NULL, date_created DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , date_modified DATETIME DEFAULT NULL, date_due DATETIME DEFAULT NULL, subject VARCHAR(255) NOT NULL, original_message VARCHAR(255) NOT NULL, resolved_date DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , closed_date DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_97A0ADA32A841BBC FOREIGN KEY (requesting_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_97A0ADA3ADF66B1A FOREIGN KEY (assigned_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_97A0ADA38359DF4E FOREIGN KEY (assigned_group_id) REFERENCES user_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_97A0ADA36713A32B FOREIGN KEY (resolved_by_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_97A0ADA3E1FA7797 FOREIGN KEY (closed_by_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_97A0ADA32A841BBC ON ticket (requesting_user_id)');
        $this->addSql('CREATE INDEX IDX_97A0ADA3ADF66B1A ON ticket (assigned_user_id)');
        $this->addSql('CREATE INDEX IDX_97A0ADA38359DF4E ON ticket (assigned_group_id)');
        $this->addSql('CREATE INDEX IDX_97A0ADA36713A32B ON ticket (resolved_by_id)');
        $this->addSql('CREATE INDEX IDX_97A0ADA3E1FA7797 ON ticket (closed_by_id)');
        $this->addSql('CREATE TABLE ticket_tag (ticket_id INTEGER NOT NULL, tag_id INTEGER NOT NULL, PRIMARY KEY(ticket_id, tag_id), CONSTRAINT FK_F06CAF700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F06CAFBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_F06CAF700047D2 ON ticket_tag (ticket_id)');
        $this->addSql('CREATE INDEX IDX_F06CAFBAD26311 ON ticket_tag (tag_id)');
        $this->addSql('CREATE TABLE ticket_action (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, friendly_name VARCHAR(255) DEFAULT NULL, full_name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, enabled BOOLEAN NOT NULL, roles_needed CLOB DEFAULT NULL --(DC2Type:array)
        , trigger_action VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE ticket_history (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, ticket_id INTEGER NOT NULL, related_user_id INTEGER NOT NULL, date_created DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_2B762919700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2B76291998771930 FOREIGN KEY (related_user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_2B762919700047D2 ON ticket_history (ticket_id)');
        $this->addSql('CREATE INDEX IDX_2B76291998771930 ON ticket_history (related_user_id)');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, state_unique_id VARCHAR(255) DEFAULT NULL, local_unique_id VARCHAR(255) DEFAULT NULL, graduation_year INTEGER DEFAULT NULL, date_created DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , username VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
        $this->addSql('CREATE TABLE user_group (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, date_created DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , assignable BOOLEAN NOT NULL)');
        $this->addSql('CREATE TABLE user_group_user (user_group_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY(user_group_id, user_id), CONSTRAINT FK_3AE4BD51ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_3AE4BD5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_3AE4BD51ED93D47 ON user_group_user (user_group_id)');
        $this->addSql('CREATE INDEX IDX_3AE4BD5A76ED395 ON user_group_user (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE status');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE ticket');
        $this->addSql('DROP TABLE ticket_tag');
        $this->addSql('DROP TABLE ticket_action');
        $this->addSql('DROP TABLE ticket_history');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_group');
        $this->addSql('DROP TABLE user_group_user');
    }
}
