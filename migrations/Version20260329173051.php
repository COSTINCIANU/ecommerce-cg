<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260329173051 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, client_name VARCHAR(255) NOT NULL, delivery_address LONGTEXT DEFAULT NULL, quantity INT NOT NULL, order_cost INT NOT NULL, taxe INT DEFAULT NULL, order_cost_ttc INT NOT NULL, is_paid TINYINT DEFAULT NULL, status VARCHAR(255) NOT NULL, carrier_price INT NOT NULL, carrier_name VARCHAR(255) NOT NULL, carrier_id INT NOT NULL, updated_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE order_details (id INT AUTO_INCREMENT NOT NULL, product_name VARCHAR(255) NOT NULL, product_description VARCHAR(255) NOT NULL, product_solde_price INT NOT NULL, product_regular_price INT NOT NULL, quantity INT NOT NULL, taxe INT DEFAULT NULL, subtotal INT NOT NULL, my_order_id INT NOT NULL, INDEX IDX_845CA2C1BFCDF877 (my_order_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE order_details ADD CONSTRAINT FK_845CA2C1BFCDF877 FOREIGN KEY (my_order_id) REFERENCES `order` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_details DROP FOREIGN KEY FK_845CA2C1BFCDF877');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_details');
    }
}
