<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260416122158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_related_products (product_source INT NOT NULL, product_target INT NOT NULL, INDEX IDX_9BB5700B3DF63ED7 (product_source), INDEX IDX_9BB5700B24136E58 (product_target), PRIMARY KEY (product_source, product_target)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE product_related_products ADD CONSTRAINT FK_9BB5700B3DF63ED7 FOREIGN KEY (product_source) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_related_products ADD CONSTRAINT FK_9BB5700B24136E58 FOREIGN KEY (product_target) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY `FK_D34A04ADA761FF2D`');
        $this->addSql('DROP INDEX IDX_D34A04ADA761FF2D ON product');
        $this->addSql('ALTER TABLE product DROP related_products_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_related_products DROP FOREIGN KEY FK_9BB5700B3DF63ED7');
        $this->addSql('ALTER TABLE product_related_products DROP FOREIGN KEY FK_9BB5700B24136E58');
        $this->addSql('DROP TABLE product_related_products');
        $this->addSql('ALTER TABLE product ADD related_products_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT `FK_D34A04ADA761FF2D` FOREIGN KEY (related_products_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_D34A04ADA761FF2D ON product (related_products_id)');
    }
}
