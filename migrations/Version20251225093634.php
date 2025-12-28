<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251225093634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment_reactions (id INT AUTO_INCREMENT NOT NULL, is_like TINYINT NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, comment_id INT NOT NULL, INDEX IDX_D10D9EE5A76ED395 (user_id), INDEX IDX_D10D9EE5F8697D13 (comment_id), UNIQUE INDEX comment_user_unique (comment_id, user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE support_messages (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, is_read TINYINT NOT NULL, created_at DATETIME NOT NULL, sender_id INT NOT NULL, receiver_id INT DEFAULT NULL, INDEX IDX_6FB495A9F624B39D (sender_id), INDEX IDX_6FB495A9CD53EDB6 (receiver_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE comment_reactions ADD CONSTRAINT FK_D10D9EE5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE comment_reactions ADD CONSTRAINT FK_D10D9EE5F8697D13 FOREIGN KEY (comment_id) REFERENCES comments (id)');
        $this->addSql('ALTER TABLE support_messages ADD CONSTRAINT FK_6FB495A9F624B39D FOREIGN KEY (sender_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE support_messages ADD CONSTRAINT FK_6FB495A9CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE articles ADD validation_status VARCHAR(50) NOT NULL, CHANGE status status VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY `FK_REACTION_USER`');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY `FK_REACTION_ARTICLE`');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY `FK_REACTION_COMMENT`');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY `FK_REACTION_USER`');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('DROP INDEX idx_reaction_user ON reactions');
        $this->addSql('CREATE INDEX IDX_38737FB3A76ED395 ON reactions (user_id)');
        $this->addSql('DROP INDEX idx_reaction_article ON reactions');
        $this->addSql('CREATE INDEX IDX_38737FB37294869C ON reactions (article_id)');
        $this->addSql('DROP INDEX idx_reaction_comment ON reactions');
        $this->addSql('CREATE INDEX IDX_38737FB3F8697D13 ON reactions (comment_id)');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT `FK_REACTION_ARTICLE` FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT `FK_REACTION_COMMENT` FOREIGN KEY (comment_id) REFERENCES comments (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT `FK_REACTION_USER` FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE users ADD is_first_article_validated TINYINT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment_reactions DROP FOREIGN KEY FK_D10D9EE5A76ED395');
        $this->addSql('ALTER TABLE comment_reactions DROP FOREIGN KEY FK_D10D9EE5F8697D13');
        $this->addSql('ALTER TABLE support_messages DROP FOREIGN KEY FK_6FB495A9F624B39D');
        $this->addSql('ALTER TABLE support_messages DROP FOREIGN KEY FK_6FB495A9CD53EDB6');
        $this->addSql('DROP TABLE comment_reactions');
        $this->addSql('DROP TABLE support_messages');
        $this->addSql('ALTER TABLE articles DROP validation_status, CHANGE status status VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3A76ED395');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3A76ED395');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB37294869C');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3F8697D13');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT `FK_REACTION_USER` FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('DROP INDEX idx_38737fb37294869c ON reactions');
        $this->addSql('CREATE INDEX IDX_REACTION_ARTICLE ON reactions (article_id)');
        $this->addSql('DROP INDEX idx_38737fb3f8697d13 ON reactions');
        $this->addSql('CREATE INDEX IDX_REACTION_COMMENT ON reactions (comment_id)');
        $this->addSql('DROP INDEX idx_38737fb3a76ed395 ON reactions');
        $this->addSql('CREATE INDEX IDX_REACTION_USER ON reactions (user_id)');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB37294869C FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3F8697D13 FOREIGN KEY (comment_id) REFERENCES comments (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users DROP is_first_article_validated');
    }
}
