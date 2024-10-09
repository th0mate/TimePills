<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241009213216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE one_signal_id (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, one_signal_id VARCHAR(255) NOT NULL, INDEX IDX_B8CF43E5FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE one_signal_id ADD CONSTRAINT FK_B8CF43E5FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE utilisateur CHANGE veut_notification veut_notification TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE one_signal_id DROP FOREIGN KEY FK_B8CF43E5FB88E14F');
        $this->addSql('DROP TABLE one_signal_id');
        $this->addSql('ALTER TABLE utilisateur CHANGE veut_notification veut_notification TINYINT(1) DEFAULT 1 NOT NULL');
    }
}
