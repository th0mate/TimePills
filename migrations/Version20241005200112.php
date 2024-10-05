<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241005200112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pilule (id INT AUTO_INCREMENT NOT NULL, proprietaire_id INT NOT NULL, libelle VARCHAR(255) NOT NULL, heure_de_prise TIME NOT NULL, temps_maxi TIME NOT NULL, nb_pilules_plaquette INT DEFAULT NULL, nb_jours_pause INT DEFAULT NULL, date_derniere_reprise DATE DEFAULT NULL, INDEX IDX_F998EA2276C50E4A (proprietaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pilule ADD CONSTRAINT FK_F998EA2276C50E4A FOREIGN KEY (proprietaire_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pilule DROP FOREIGN KEY FK_F998EA2276C50E4A');
        $this->addSql('DROP TABLE pilule');
    }
}
