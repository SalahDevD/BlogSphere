<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260102_AddSupportMessageFields extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add subject, message_type, and parent_message fields to support_messages table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE support_messages ADD COLUMN subject VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE support_messages ADD COLUMN message_type VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE support_messages ADD COLUMN parent_message_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE support_messages ADD CONSTRAINT FK_support_messages_parent FOREIGN KEY (parent_message_id) REFERENCES support_messages (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_support_messages_parent ON support_messages (parent_message_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE support_messages DROP FOREIGN KEY FK_support_messages_parent');
        $this->addSql('DROP INDEX IDX_support_messages_parent ON support_messages');
        $this->addSql('ALTER TABLE support_messages DROP COLUMN subject');
        $this->addSql('ALTER TABLE support_messages DROP COLUMN message_type');
        $this->addSql('ALTER TABLE support_messages DROP COLUMN parent_message_id');
    }
}
