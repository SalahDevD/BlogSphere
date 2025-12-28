<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251225103813 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB37294869C FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3F8697D13 FOREIGN KEY (comment_id) REFERENCES comments (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_reaction_user ON reactions');
        $this->addSql('CREATE INDEX IDX_38737FB3A76ED395 ON reactions (user_id)');
        $this->addSql('DROP INDEX idx_reaction_article ON reactions');
        $this->addSql('CREATE INDEX IDX_38737FB37294869C ON reactions (article_id)');
        $this->addSql('DROP INDEX idx_reaction_comment ON reactions');
        $this->addSql('CREATE INDEX IDX_38737FB3F8697D13 ON reactions (comment_id)');
        $this->addSql('ALTER TABLE users CHANGE is_first_article_validated first_article_validated TINYINT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3A76ED395');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB37294869C');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3F8697D13');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3A76ED395');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB37294869C');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3F8697D13');
        $this->addSql('DROP INDEX idx_38737fb3a76ed395 ON reactions');
        $this->addSql('CREATE INDEX IDX_REACTION_USER ON reactions (user_id)');
        $this->addSql('DROP INDEX idx_38737fb37294869c ON reactions');
        $this->addSql('CREATE INDEX IDX_REACTION_ARTICLE ON reactions (article_id)');
        $this->addSql('DROP INDEX idx_38737fb3f8697d13 ON reactions');
        $this->addSql('CREATE INDEX IDX_REACTION_COMMENT ON reactions (comment_id)');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB37294869C FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3F8697D13 FOREIGN KEY (comment_id) REFERENCES comments (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users CHANGE first_article_validated is_first_article_validated TINYINT DEFAULT 0 NOT NULL');
    }
}
