<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250812142244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, created_by_user_id INT DEFAULT NULL, ticket_id INT DEFAULT NULL, user_id INT DEFAULT NULL, date_created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_modified DATETIME DEFAULT NULL, subject VARCHAR(255) DEFAULT NULL, comment VARCHAR(255) NOT NULL, INDEX IDX_9474526C7D182D95 (created_by_user_id), INDEX IDX_9474526C700047D2 (ticket_id), INDEX IDX_9474526CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE config (id INT AUTO_INCREMENT NOT NULL, config_key VARCHAR(255) NOT NULL, config_value VARCHAR(255) NOT NULL, default_value VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, friendly_name VARCHAR(255) DEFAULT NULL, full_name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, INDEX IDX_5E9E89CB727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE status (id INT AUTO_INCREMENT NOT NULL, friendly_name VARCHAR(255) DEFAULT NULL, full_name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, deletable TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, friendly_name VARCHAR(255) DEFAULT NULL, full_name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, deletable TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ticket (id INT AUTO_INCREMENT NOT NULL, requesting_user_id INT DEFAULT NULL, assigned_user_id INT DEFAULT NULL, assigned_group_id INT DEFAULT NULL, resolved_by_id INT DEFAULT NULL, closed_by_id INT DEFAULT NULL, status_id INT NOT NULL, modified_by_id INT DEFAULT NULL, canceled_by_id INT DEFAULT NULL, date_created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_modified DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_due DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', subject VARCHAR(255) NOT NULL, original_message VARCHAR(255) NOT NULL, resolved_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', closed_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', canceled_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_97A0ADA32A841BBC (requesting_user_id), INDEX IDX_97A0ADA3ADF66B1A (assigned_user_id), INDEX IDX_97A0ADA38359DF4E (assigned_group_id), INDEX IDX_97A0ADA36713A32B (resolved_by_id), INDEX IDX_97A0ADA3E1FA7797 (closed_by_id), INDEX IDX_97A0ADA36BF700BD (status_id), INDEX IDX_97A0ADA399049ECE (modified_by_id), INDEX IDX_97A0ADA31418957 (canceled_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ticket_tag (ticket_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_F06CAF700047D2 (ticket_id), INDEX IDX_F06CAFBAD26311 (tag_id), PRIMARY KEY(ticket_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ticket_action (id INT AUTO_INCREMENT NOT NULL, friendly_name VARCHAR(255) DEFAULT NULL, full_name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) NOT NULL, roles_needed LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', trigger_action VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ticket_history (id INT AUTO_INCREMENT NOT NULL, ticket_id INT NOT NULL, related_user_id INT NOT NULL, date_created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', message VARCHAR(255) DEFAULT NULL, subject VARCHAR(255) DEFAULT NULL, INDEX IDX_2B762919700047D2 (ticket_id), INDEX IDX_2B76291998771930 (related_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, state_unique_id VARCHAR(255) DEFAULT NULL, local_unique_id VARCHAR(255) DEFAULT NULL, graduation_year INT DEFAULT NULL, date_created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', username VARCHAR(255) NOT NULL, first_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) NOT NULL, can_log_in TINYINT(1) NOT NULL, default_starting_page VARCHAR(255) DEFAULT NULL, last_logon_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', lsat_logon_ip VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, date_created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', assignable TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_group_user (user_group_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_3AE4BD51ED93D47 (user_group_id), INDEX IDX_3AE4BD5A76ED395 (user_id), PRIMARY KEY(user_group_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C7D182D95 FOREIGN KEY (created_by_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB727ACA70 FOREIGN KEY (parent_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA32A841BBC FOREIGN KEY (requesting_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3ADF66B1A FOREIGN KEY (assigned_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA38359DF4E FOREIGN KEY (assigned_group_id) REFERENCES user_group (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA36713A32B FOREIGN KEY (resolved_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3E1FA7797 FOREIGN KEY (closed_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA36BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA399049ECE FOREIGN KEY (modified_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA31418957 FOREIGN KEY (canceled_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE ticket_tag ADD CONSTRAINT FK_F06CAF700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ticket_tag ADD CONSTRAINT FK_F06CAFBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ticket_history ADD CONSTRAINT FK_2B762919700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id)');
        $this->addSql('ALTER TABLE ticket_history ADD CONSTRAINT FK_2B76291998771930 FOREIGN KEY (related_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_group_user ADD CONSTRAINT FK_3AE4BD51ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_group_user ADD CONSTRAINT FK_3AE4BD5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C7D182D95');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C700047D2');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CA76ED395');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CB727ACA70');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA32A841BBC');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3ADF66B1A');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA38359DF4E');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA36713A32B');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3E1FA7797');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA36BF700BD');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA399049ECE');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA31418957');
        $this->addSql('ALTER TABLE ticket_tag DROP FOREIGN KEY FK_F06CAF700047D2');
        $this->addSql('ALTER TABLE ticket_tag DROP FOREIGN KEY FK_F06CAFBAD26311');
        $this->addSql('ALTER TABLE ticket_history DROP FOREIGN KEY FK_2B762919700047D2');
        $this->addSql('ALTER TABLE ticket_history DROP FOREIGN KEY FK_2B76291998771930');
        $this->addSql('ALTER TABLE user_group_user DROP FOREIGN KEY FK_3AE4BD51ED93D47');
        $this->addSql('ALTER TABLE user_group_user DROP FOREIGN KEY FK_3AE4BD5A76ED395');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE config');
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
