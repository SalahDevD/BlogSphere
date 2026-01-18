<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260109105249 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chat_messages DROP FOREIGN KEY `FK_EF20C9A6A76ED395`');
        $this->addSql('DROP INDEX idx_b2a1d864a76ed395 ON chat_messages');
        $this->addSql('CREATE INDEX IDX_EF20C9A6A76ED395 ON chat_messages (user_id)');
        $this->addSql('ALTER TABLE chat_messages ADD CONSTRAINT `FK_EF20C9A6A76ED395` FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE support_messages DROP FOREIGN KEY `FK_6FB495A9CD53EDB6`');
        $this->addSql('ALTER TABLE support_messages DROP FOREIGN KEY `FK_6FB495A9F624B39D`');
        $this->addSql('ALTER TABLE support_messages DROP FOREIGN KEY `FK_support_messages_parent`');
        $this->addSql('ALTER TABLE support_messages ADD CONSTRAINT FK_6FB495A9CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE support_messages ADD CONSTRAINT FK_6FB495A9F624B39D FOREIGN KEY (sender_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_support_messages_parent ON support_messages');
        $this->addSql('CREATE INDEX IDX_6FB495A914399779 ON support_messages (parent_message_id)');
        $this->addSql('ALTER TABLE support_messages ADD CONSTRAINT `FK_support_messages_parent` FOREIGN KEY (parent_message_id) REFERENCES support_messages (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chat_messages DROP FOREIGN KEY FK_EF20C9A6A76ED395');
        $this->addSql('DROP INDEX idx_ef20c9a6a76ed395 ON chat_messages');
        $this->addSql('CREATE INDEX IDX_B2A1D864A76ED395 ON chat_messages (user_id)');
        $this->addSql('ALTER TABLE chat_messages ADD CONSTRAINT FK_EF20C9A6A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE support_messages DROP FOREIGN KEY FK_6FB495A9F624B39D');
        $this->addSql('ALTER TABLE support_messages DROP FOREIGN KEY FK_6FB495A9CD53EDB6');
        $this->addSql('ALTER TABLE support_messages DROP FOREIGN KEY FK_6FB495A914399779');
        $this->addSql('ALTER TABLE support_messages ADD CONSTRAINT `FK_6FB495A9F624B39D` FOREIGN KEY (sender_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE support_messages ADD CONSTRAINT `FK_6FB495A9CD53EDB6` FOREIGN KEY (receiver_id) REFERENCES users (id)');
        $this->addSql('DROP INDEX idx_6fb495a914399779 ON support_messages');
        $this->addSql('CREATE INDEX IDX_support_messages_parent ON support_messages (parent_message_id)');
        $this->addSql('ALTER TABLE support_messages ADD CONSTRAINT FK_6FB495A914399779 FOREIGN KEY (parent_message_id) REFERENCES support_messages (id) ON DELETE SET NULL');
    }
}
