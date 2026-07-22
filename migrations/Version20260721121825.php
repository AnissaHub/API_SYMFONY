<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260721121825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cars ADD kilometrage INT DEFAULT NULL, ADD prix NUMERIC(10, 2) NOT NULL, DROP date_entree, DROP date_sortie, CHANGE observations description LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cars ADD date_entree DATETIME NOT NULL, ADD date_sortie DATETIME DEFAULT NULL, DROP kilometrage, DROP prix, CHANGE description observations LONGTEXT DEFAULT NULL');
    }
}
