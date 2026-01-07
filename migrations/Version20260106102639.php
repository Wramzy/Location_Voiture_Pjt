<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260106102639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, telephone VARCHAR(50) NOT NULL, numero_permis VARCHAR(100) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE rental (id INT AUTO_INCREMENT NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, statut VARCHAR(50) NOT NULL, client_id INT NOT NULL, vehicle_id INT NOT NULL, INDEX IDX_1619C27D19EB6921 (client_id), INDEX IDX_1619C27D545317D1 (vehicle_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE vehicle (id INT AUTO_INCREMENT NOT NULL, marque VARCHAR(255) NOT NULL, modele VARCHAR(255) NOT NULL, annee INT NOT NULL, immatriculation VARCHAR(50) NOT NULL, statut VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_1B80E486BE73422E (immatriculation), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE rental ADD CONSTRAINT FK_1619C27D19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE rental ADD CONSTRAINT FK_1619C27D545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rental DROP FOREIGN KEY FK_1619C27D19EB6921');
        $this->addSql('ALTER TABLE rental DROP FOREIGN KEY FK_1619C27D545317D1');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE rental');
        $this->addSql('DROP TABLE vehicle');
    }
}
