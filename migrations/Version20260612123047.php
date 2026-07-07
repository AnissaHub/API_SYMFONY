<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260612123047 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, total DOUBLE PRECISION NOT NULL, statut VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6EEAA67DFB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commande_car (commande_id INT NOT NULL, car_id INT NOT NULL, INDEX IDX_706DF1CD82EA2E54 (commande_id), INDEX IDX_706DF1CDC3C6F69F (car_id), PRIMARY KEY(commande_id, car_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs (id)');
        $this->addSql('ALTER TABLE commande_car ADD CONSTRAINT FK_706DF1CD82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commande_car ADD CONSTRAINT FK_706DF1CDC3C6F69F FOREIGN KEY (car_id) REFERENCES cars (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DFB88E14F');
        $this->addSql('ALTER TABLE commande_car DROP FOREIGN KEY FK_706DF1CD82EA2E54');
        $this->addSql('ALTER TABLE commande_car DROP FOREIGN KEY FK_706DF1CDC3C6F69F');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE commande_car');
    }
}
