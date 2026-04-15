<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration unique — Schéma complet de la BDD C.G E-commerce
 * Auteur : Gheorghina Costincianu
 */
final class Version20260415120100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Schéma complet de la BDD C.G E-commerce';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, client_name VARCHAR(255) NOT NULL, street VARCHAR(255) NOT NULL, code_postal VARCHAR(255) DEFAULT NULL, city VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, more_details LONGTEXT DEFAULT NULL, updated_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, address_type VARCHAR(255) DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_D4E6F81A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE carrier (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, price INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, is_mega TINYINT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE collection (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, button_text VARCHAR(255) NOT NULL, button_link VARCHAR(255) NOT NULL, image_url VARCHAR(255) NOT NULL, updated_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, is_mega TINYINT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, rating INT NOT NULL, content LONGTEXT NOT NULL, email VARCHAR(255) NOT NULL, is_published TINYINT NOT NULL, created_at DATETIME NOT NULL, author_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_9474526CF675F31B (author_id), INDEX IDX_9474526C4584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, subject VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL, response LONGTEXT DEFAULT NULL, responded_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, client_name VARCHAR(255) NOT NULL, billing_address LONGTEXT DEFAULT NULL, shipping_address LONGTEXT DEFAULT NULL, quantity INT NOT NULL, order_cost_ht INT NOT NULL, taxe INT DEFAULT NULL, order_cost_ttc INT NOT NULL, is_paid TINYINT DEFAULT NULL, status VARCHAR(255) NOT NULL, carrier_price INT NOT NULL, carrier_name VARCHAR(255) NOT NULL, carrier_id INT NOT NULL, updated_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, stripe_client_secret VARCHAR(255) DEFAULT NULL, paypal_client_secret VARCHAR(255) DEFAULT NULL, payment_method VARCHAR(255) DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_F5299398A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE order_details (id INT AUTO_INCREMENT NOT NULL, product_name VARCHAR(255) NOT NULL, product_description VARCHAR(255) NOT NULL, product_solde_price INT NOT NULL, product_regular_price INT NOT NULL, quantity INT NOT NULL, taxe INT DEFAULT NULL, subtotal INT NOT NULL, updated_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, my_order_id INT NOT NULL, INDEX IDX_845CA2C1BFCDF877 (my_order_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE page (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, is_head TINYINT DEFAULT NULL, is_foot TINYINT DEFAULT NULL, updated_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE payment_method (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, more_description LONGTEXT DEFAULT NULL, image_url VARCHAR(255) DEFAULT NULL, test_public_api_key LONGTEXT DEFAULT NULL, test_private_api_key LONGTEXT DEFAULT NULL, prod_public_api_key LONGTEXT DEFAULT NULL, prod_private_api_key LONGTEXT DEFAULT NULL, updated_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, test_base_url LONGTEXT DEFAULT NULL, prod_base_url LONGTEXT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, more_description LONGTEXT DEFAULT NULL, additional_infos LONGTEXT DEFAULT NULL, stock INT DEFAULT NULL, solde_price INT NOT NULL, regular_price INT NOT NULL, image_urls LONGTEXT NOT NULL, brand VARCHAR(255) DEFAULT NULL, is_available TINYINT DEFAULT NULL, is_best_seller TINYINT DEFAULT NULL, is_new_arrival TINYINT DEFAULT NULL, is_featured TINYINT DEFAULT NULL, is_special_offer TINYINT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, related_products_id INT DEFAULT NULL, INDEX IDX_D34A04ADA761FF2D (related_products_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product_category (product_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_CDFC73564584665A (product_id), INDEX IDX_CDFC735612469DE2 (category_id), PRIMARY KEY (product_id, category_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE setting (id INT AUTO_INCREMENT NOT NULL, website_name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, currency VARCHAR(255) NOT NULL, taxe_rate INT DEFAULT NULL, logo VARCHAR(255) NOT NULL, street VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, code_postal VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, updated_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, phone VARCHAR(255) DEFAULT NULL, facebook_link VARCHAR(255) DEFAULT NULL, youtube_link VARCHAR(255) DEFAULT NULL, instagram_link VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, copyright VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sliders (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, button_text VARCHAR(255) NOT NULL, button_link VARCHAR(255) NOT NULL, image_url VARCHAR(255) NOT NULL, updated_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, full_name VARCHAR(255) DEFAULT NULL, civility VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, is_verified TINYINT NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F81A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE order_details ADD CONSTRAINT FK_845CA2C1BFCDF877 FOREIGN KEY (my_order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04ADA761FF2D FOREIGN KEY (related_products_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_category ADD CONSTRAINT FK_CDFC73564584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_category ADD CONSTRAINT FK_CDFC735612469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F81A76ED395');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF675F31B');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C4584665A');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('ALTER TABLE order_details DROP FOREIGN KEY FK_845CA2C1BFCDF877');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADA761FF2D');
        $this->addSql('ALTER TABLE product_category DROP FOREIGN KEY FK_CDFC73564584665A');
        $this->addSql('ALTER TABLE product_category DROP FOREIGN KEY FK_CDFC735612469DE2');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE carrier');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE collection');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_details');
        $this->addSql('DROP TABLE page');
        $this->addSql('DROP TABLE payment_method');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_category');
        $this->addSql('DROP TABLE setting');
        $this->addSql('DROP TABLE sliders');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}