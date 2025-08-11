<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250811133943 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket ADD cancelled_by_id INT DEFAULT NULL, ADD cancelled_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3187B2D12 FOREIGN KEY (cancelled_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_97A0ADA3187B2D12 ON ticket (cancelled_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3187B2D12');
        $this->addSql('DROP INDEX IDX_97A0ADA3187B2D12 ON ticket');
        $this->addSql('ALTER TABLE ticket DROP cancelled_by_id, DROP cancelled_date');
    }
}
