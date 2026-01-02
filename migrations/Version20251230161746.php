<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251230161746 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_tag DROP FOREIGN KEY `FK_919694F97294869C`');
        $this->addSql('ALTER TABLE article_tag DROP FOREIGN KEY `FK_919694F9BAD26311`');
        $this->addSql('DROP TABLE article_tag');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY `FK_BFDD316812469DE2`');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY `FK_BFDD3168F675F31B`');
        $this->addSql('ALTER TABLE article DROP status, DROP published_at, DROP comments_enabled, CHANGE category_id category_id INT NOT NULL, CHANGE validation_status validation_status VARCHAR(50) NOT NULL');
        $this->addSql('DROP INDEX idx_bfdd316812469de2 ON article');
        $this->addSql('CREATE INDEX IDX_23A0E6612469DE2 ON article (category_id)');
        $this->addSql('DROP INDEX idx_bfdd3168f675f31b ON article');
        $this->addSql('CREATE INDEX IDX_23A0E66F675F31B ON article (author_id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT `FK_BFDD316812469DE2` FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT `FK_BFDD3168F675F31B` FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY `FK_5F9E962A727ACA70`');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY `FK_5F9E962A7294869C`');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY `FK_5F9E962AF675F31B`');
        $this->addSql('ALTER TABLE comment CHANGE status status VARCHAR(20) NOT NULL');
        $this->addSql('DROP INDEX idx_5f9e962af675f31b ON comment');
        $this->addSql('CREATE INDEX IDX_9474526CF675F31B ON comment (author_id)');
        $this->addSql('DROP INDEX idx_5f9e962a7294869c ON comment');
        $this->addSql('CREATE INDEX IDX_9474526C7294869C ON comment (article_id)');
        $this->addSql('DROP INDEX idx_5f9e962a727aca70 ON comment');
        $this->addSql('CREATE INDEX IDX_9474526C727ACA70 ON comment (parent_id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT `FK_5F9E962A727ACA70` FOREIGN KEY (parent_id) REFERENCES comment (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT `FK_5F9E962A7294869C` FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT `FK_5F9E962AF675F31B` FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('DROP INDEX comment_user_unique ON comment_reactions');
        $this->addSql('CREATE UNIQUE INDEX unique_user_comment ON comment_reactions (user_id, comment_id)');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY `FK_38737FB37294869C`');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY `FK_38737FB3F8697D13`');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB37294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT FK_38737FB3F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY `FK_F11FA7457294869C`');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY `FK_F11FA745F8697D13`');
        $this->addSql('ALTER TABLE reports CHANGE status status VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA7457294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA745F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id)');
        $this->addSql('ALTER TABLE support_messages ADD image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_images DROP FOREIGN KEY `user_images_ibfk_1`');
        $this->addSql('DROP INDEX idx_user_id ON user_images');
        $this->addSql('CREATE INDEX IDX_854DA557A76ED395 ON user_images (user_id)');
        $this->addSql('ALTER TABLE user_images ADD CONSTRAINT `user_images_ibfk_1` FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users CHANGE is_active is_active TINYINT DEFAULT 1 NOT NULL, CHANGE is_validated is_validated TINYINT DEFAULT 0 NOT NULL, CHANGE first_name first_name VARCHAR(255) NOT NULL, CHANGE last_name last_name VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article_tag (article_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_919694F97294869C (article_id), INDEX IDX_919694F9BAD26311 (tag_id), PRIMARY KEY (article_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE article_tag ADD CONSTRAINT `FK_919694F97294869C` FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_tag ADD CONSTRAINT `FK_919694F9BAD26311` FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6612469DE2');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66F675F31B');
        $this->addSql('ALTER TABLE article ADD status VARCHAR(50) DEFAULT NULL, ADD published_at DATETIME DEFAULT NULL, ADD comments_enabled TINYINT NOT NULL, CHANGE validation_status validation_status VARCHAR(50) DEFAULT NULL, CHANGE category_id category_id INT DEFAULT NULL');
        $this->addSql('DROP INDEX idx_23a0e66f675f31b ON article');
        $this->addSql('CREATE INDEX IDX_BFDD3168F675F31B ON article (author_id)');
        $this->addSql('DROP INDEX idx_23a0e6612469de2 ON article');
        $this->addSql('CREATE INDEX IDX_BFDD316812469DE2 ON article (category_id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6612469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66F675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CF675F31B');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C7294869C');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C727ACA70');
        $this->addSql('ALTER TABLE comment CHANGE status status VARCHAR(50) NOT NULL');
        $this->addSql('DROP INDEX idx_9474526c727aca70 ON comment');
        $this->addSql('CREATE INDEX IDX_5F9E962A727ACA70 ON comment (parent_id)');
        $this->addSql('DROP INDEX idx_9474526cf675f31b ON comment');
        $this->addSql('CREATE INDEX IDX_5F9E962AF675F31B ON comment (author_id)');
        $this->addSql('DROP INDEX idx_9474526c7294869c ON comment');
        $this->addSql('CREATE INDEX IDX_5F9E962A7294869C ON comment (article_id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CF675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C727ACA70 FOREIGN KEY (parent_id) REFERENCES comment (id)');
        $this->addSql('DROP INDEX unique_user_comment ON comment_reactions');
        $this->addSql('CREATE UNIQUE INDEX comment_user_unique ON comment_reactions (comment_id, user_id)');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB37294869C');
        $this->addSql('ALTER TABLE reactions DROP FOREIGN KEY FK_38737FB3F8697D13');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT `FK_38737FB37294869C` FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE reactions ADD CONSTRAINT `FK_38737FB3F8697D13` FOREIGN KEY (comment_id) REFERENCES comment (id)');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA7457294869C');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745F8697D13');
        $this->addSql('ALTER TABLE reports CHANGE status status VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT `FK_F11FA7457294869C` FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT `FK_F11FA745F8697D13` FOREIGN KEY (comment_id) REFERENCES comment (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE support_messages DROP image');
        $this->addSql('ALTER TABLE users CHANGE first_name first_name VARCHAR(255) DEFAULT \'\' NOT NULL, CHANGE last_name last_name VARCHAR(255) DEFAULT \'\' NOT NULL, CHANGE is_active is_active TINYINT NOT NULL, CHANGE is_validated is_validated TINYINT NOT NULL');
        $this->addSql('ALTER TABLE user_images DROP FOREIGN KEY FK_854DA557A76ED395');
        $this->addSql('DROP INDEX idx_854da557a76ed395 ON user_images');
        $this->addSql('CREATE INDEX idx_user_id ON user_images (user_id)');
        $this->addSql('ALTER TABLE user_images ADD CONSTRAINT FK_854DA557A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }
}
