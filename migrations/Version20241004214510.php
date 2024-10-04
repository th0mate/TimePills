<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241004214510 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pilule ADD nb_pilules_plaquette INT DEFAULT NULL, ADD nb_jours_pause INT DEFAULT NULL, DROP calendrier');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pilule ADD calendrier VARCHAR(255) DEFAULT NULL, DROP nb_pilules_plaquette, DROP nb_jours_pause');
    }
}
