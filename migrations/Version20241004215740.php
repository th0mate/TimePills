<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241004215740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pilule ADD proprietaire_id INT NOT NULL');
        $this->addSql('ALTER TABLE pilule ADD CONSTRAINT FK_F998EA2276C50E4A FOREIGN KEY (proprietaire_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_F998EA2276C50E4A ON pilule (proprietaire_id)');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B37CD5757');
        $this->addSql('DROP INDEX UNIQ_1D1C63B37CD5757 ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur DROP pilule_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateur ADD pilule_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B37CD5757 FOREIGN KEY (pilule_id) REFERENCES pilule (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B37CD5757 ON utilisateur (pilule_id)');
        $this->addSql('ALTER TABLE pilule DROP FOREIGN KEY FK_F998EA2276C50E4A');
        $this->addSql('DROP INDEX IDX_F998EA2276C50E4A ON pilule');
        $this->addSql('ALTER TABLE pilule DROP proprietaire_id');
    }
}
