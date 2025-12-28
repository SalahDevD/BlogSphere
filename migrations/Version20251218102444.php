<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251218000000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE users (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            bio LONGTEXT DEFAULT NULL,
            photo VARCHAR(255) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL,
            is_validated TINYINT(1) NOT NULL,
            UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE categories (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            UNIQUE INDEX UNIQ_3AF346685E237E06 (name),
            UNIQUE INDEX UNIQ_3AF34668989D9B62 (slug),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE tags (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL,
            UNIQUE INDEX UNIQ_6FBC94265E237E06 (name),
            UNIQUE INDEX UNIQ_6FBC9426989D9B62 (slug),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE articles (
            id INT AUTO_INCREMENT NOT NULL,
            author_id INT NOT NULL,
            category_id INT DEFAULT NULL,
            title VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            image VARCHAR(255) DEFAULT NULL,
            status VARCHAR(50) NOT NULL,
            created_at DATETIME NOT NULL,
            published_at DATETIME DEFAULT NULL,
            comments_enabled TINYINT(1) NOT NULL,
            INDEX IDX_BFDD3168F675F31B (author_id),
            INDEX IDX_BFDD316812469DE2 (category_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE article_tag (
            article_id INT NOT NULL,
            tag_id INT NOT NULL,
            INDEX IDX_919694F97294869C (article_id),
            INDEX IDX_919694F9BAD26311 (tag_id),
            PRIMARY KEY(article_id, tag_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE comments (
            id INT AUTO_INCREMENT NOT NULL,
            author_id INT NOT NULL,
            article_id INT NOT NULL,
            parent_id INT DEFAULT NULL,
            content LONGTEXT NOT NULL,
            status VARCHAR(50) NOT NULL,
            created_at DATETIME NOT NULL,
            INDEX IDX_5F9E962AF675F31B (author_id),
            INDEX IDX_5F9E962A7294869C (article_id),
            INDEX IDX_5F9E962A727ACA70 (parent_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE reports (
            id INT AUTO_INCREMENT NOT NULL,
            reporter_id INT NOT NULL,
            article_id INT DEFAULT NULL,
            comment_id INT DEFAULT NULL,
            reason LONGTEXT NOT NULL,
            status VARCHAR(50) NOT NULL,
            created_at DATETIME NOT NULL,
            INDEX IDX_F11FA745E1CFE6F5 (reporter_id),
            INDEX IDX_F11FA7457294869C (article_id),
            INDEX IDX_F11FA745F8697D13 (comment_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE articles ADD CONSTRAINT FK_BFDD3168F675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE articles ADD CONSTRAINT FK_BFDD316812469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE article_tag ADD CONSTRAINT FK_919694F97294869C FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_tag ADD CONSTRAINT FK_919694F9BAD26311 FOREIGN KEY (tag_id) REFERENCES tags (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AF675F31B FOREIGN KEY (author_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962A7294869C FOREIGN KEY (article_id) REFERENCES articles (id)');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962A727ACA70 FOREIGN KEY (parent_id) REFERENCES comments (id)');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA745E1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA7457294869C FOREIGN KEY (article_id) REFERENCES articles (id)');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA745F8697D13 FOREIGN KEY (comment_id) REFERENCES comments (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE articles DROP FOREIGN KEY FK_BFDD3168F675F31B');
        $this->addSql('ALTER TABLE articles DROP FOREIGN KEY FK_BFDD316812469DE2');
        $this->addSql('ALTER TABLE article_tag DROP FOREIGN KEY FK_919694F97294869C');
        $this->addSql('ALTER TABLE article_tag DROP FOREIGN KEY FK_919694F9BAD26311');
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962AF675F31B');
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962A7294869C');
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962A727ACA70');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745E1CFE6F5');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA7457294869C');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745F8697D13');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE tags');
        $this->addSql('DROP TABLE articles');
        $this->addSql('DROP TABLE article_tag');
        $this->addSql('DROP TABLE comments');
        $this->addSql('DROP TABLE reports');
    }
}
