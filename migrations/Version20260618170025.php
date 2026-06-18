<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260618170025 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reminder (id INT AUTO_INCREMENT NOT NULL, message LONGTEXT NOT NULL, created_at DATETIME NOT NULL, loan_id INT NOT NULL, INDEX IDX_40374F40CE73868F (loan_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE reminder ADD CONSTRAINT FK_40374F40CE73868F FOREIGN KEY (loan_id) REFERENCES loan (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reminder DROP FOREIGN KEY FK_40374F40CE73868F');
        $this->addSql('DROP TABLE reminder');
    }
}
