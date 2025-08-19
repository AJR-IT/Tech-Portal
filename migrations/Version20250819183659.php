<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250819183659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE device (id INT AUTO_INCREMENT NOT NULL, assigned_to_id INT DEFAULT NULL, asset_tag VARCHAR(255) DEFAULT NULL, serial_number VARCHAR(255) DEFAULT NULL, date_created DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_modified DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_purchased DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_warranty_start DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_warranty_end DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', decommissioned TINYINT(1) DEFAULT NULL, INDEX IDX_92FB68EF4BD7827 (assigned_to_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68EF4BD7827 FOREIGN KEY (assigned_to_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE device DROP FOREIGN KEY FK_92FB68EF4BD7827');
        $this->addSql('DROP TABLE device');
    }
}
