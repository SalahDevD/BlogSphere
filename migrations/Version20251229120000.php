<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251229120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add first_name and last_name to users table, and create user_image table';
    }

    public function up(Schema $schema): void
    {
        // Add first_name and last_name columns to users table
        $this->addSql('ALTER TABLE users ADD first_name VARCHAR(255) NOT NULL DEFAULT ""');
        $this->addSql('ALTER TABLE users ADD last_name VARCHAR(255) NOT NULL DEFAULT ""');

        // Create user_image table
        $this->addSql('CREATE TABLE user_image (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, filename VARCHAR(255) NOT NULL, uploaded_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_profile TINYINT(1) NOT NULL DEFAULT 0, INDEX IDX_6B48B33A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_image ADD CONSTRAINT FK_6B48B33A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Drop user_image table
        $this->addSql('DROP TABLE user_image');

        // Remove first_name and last_name columns from users table
        $this->addSql('ALTER TABLE users DROP first_name, DROP last_name');
    }
}
