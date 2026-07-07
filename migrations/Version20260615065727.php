<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260615065727 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commande_item (id INT AUTO_INCREMENT NOT NULL, commande_id INT NOT NULL, car_id INT NOT NULL, quantite INT NOT NULL, prix DOUBLE PRECISION NOT NULL, INDEX IDX_747724FD82EA2E54 (commande_id), INDEX IDX_747724FDC3C6F69F (car_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commande_item ADD CONSTRAINT FK_747724FD82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE commande_item ADD CONSTRAINT FK_747724FDC3C6F69F FOREIGN KEY (car_id) REFERENCES cars (id)');
        $this->addSql('ALTER TABLE commande_car DROP FOREIGN KEY FK_706DF1CD82EA2E54');
        $this->addSql('ALTER TABLE commande_car DROP FOREIGN KEY FK_706DF1CDC3C6F69F');
        $this->addSql('DROP TABLE commande_car');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commande_car (commande_id INT NOT NULL, car_id INT NOT NULL, INDEX IDX_706DF1CD82EA2E54 (commande_id), INDEX IDX_706DF1CDC3C6F69F (car_id), PRIMARY KEY(commande_id, car_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE commande_car ADD CONSTRAINT FK_706DF1CD82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commande_car ADD CONSTRAINT FK_706DF1CDC3C6F69F FOREIGN KEY (car_id) REFERENCES cars (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commande_item DROP FOREIGN KEY FK_747724FD82EA2E54');
        $this->addSql('ALTER TABLE commande_item DROP FOREIGN KEY FK_747724FDC3C6F69F');
        $this->addSql('DROP TABLE commande_item');
    }
}
