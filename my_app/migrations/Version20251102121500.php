<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Creates address table linked to user.
 */
final class Version20251102121500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create address table with user relation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, street VARCHAR(255) NOT NULL, city VARCHAR(100) NOT NULL, state VARCHAR(100) DEFAULT NULL, country VARCHAR(100) NOT NULL, postal_code VARCHAR(20) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, type VARCHAR(20) NOT NULL, INDEX IDX_D4E6F818A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F818A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F818A76ED395');
        $this->addSql('DROP TABLE address');
    }
}
