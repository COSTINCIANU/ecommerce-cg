<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajout des champs response et responded_at à la table contact.
 * Permet à l'admin de répondre aux messages depuis le dashboard.
 */
final class Version20260412173318 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des champs response et responded_at à la table contact';
    }

    public function up(Schema $schema): void
    {
        // Vérification que les colonnes n'existent pas déjà
        // avant de les ajouter (évite les erreurs sur BDD existante)
        // $this->addSql('
        //     ALTER TABLE contact 
        //     ADD COLUMN IF NOT EXISTS response LONGTEXT DEFAULT NULL,
        //     ADD COLUMN IF NOT EXISTS responded_at DATETIME DEFAULT NULL
        // ');

        // ✅ Syntaxe compatible MySQL 8.0 et MariaDB
    $this->addSql('ALTER TABLE contact ADD response LONGTEXT DEFAULT NULL, ADD responded_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact DROP response, DROP responded_at');
    }
}