<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251102140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adjust order and order_item tables for checkout flow enhancements.';
    }

    public function up(Schema $schema): void
    {
        // Ensure updated_at exists and mirrors created_at for existing orders
        $this->addSql('ALTER TABLE `order` ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('UPDATE `order` SET updated_at = created_at WHERE updated_at IS NULL');
        $this->addSql('ALTER TABLE `order` CHANGE updated_at updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');

        // Adjust monetary precision and make paid_at nullable
        $this->addSql('ALTER TABLE `order` CHANGE total total NUMERIC(10, 2) NOT NULL, CHANGE paid_at paid_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');

        // Link order items to their parent order
        $this->addSql('ALTER TABLE order_item ADD order_ref_id INT NOT NULL');
        $this->addSql('ALTER TABLE order_item ADD CONSTRAINT FK_52EA1F09E238517C FOREIGN KEY (order_ref_id) REFERENCES `order` (id)');
        $this->addSql('CREATE INDEX IDX_52EA1F09E238517C ON order_item (order_ref_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE order_item DROP FOREIGN KEY FK_52EA1F09E238517C');
        $this->addSql('DROP INDEX IDX_52EA1F09E238517C ON order_item');
        $this->addSql('ALTER TABLE order_item DROP order_ref_id');

        $this->addSql('ALTER TABLE `order` DROP updated_at');
        $this->addSql('ALTER TABLE `order` CHANGE total total NUMERIC(10, 0) NOT NULL, CHANGE paid_at paid_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }
}
