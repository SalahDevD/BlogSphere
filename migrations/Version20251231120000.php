<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251231120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create chat_messages table for chatbot conversations';
    }

    public function up(Schema $schema): void
    {
        // Check if table already exists to avoid errors
        if (!$schema->hasTable('chat_messages')) {
            $this->addSql('CREATE TABLE chat_messages (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, user_message LONGTEXT NOT NULL, bot_response LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_B2A1D864A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
            $this->addSql('ALTER TABLE chat_messages ADD CONSTRAINT FK_B2A1D864A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chat_messages DROP FOREIGN KEY FK_B2A1D864A76ED395');
        $this->addSql('DROP TABLE chat_messages');
    }
}
