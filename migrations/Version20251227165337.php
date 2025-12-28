<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251227165337 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY `FK_F11FA7457294869C`');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY `FK_F11FA745F8697D13`');
        $this->addSql('ALTER TABLE reports CHANGE status status VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA7457294869C FOREIGN KEY (article_id) REFERENCES articles (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT FK_F11FA745F8697D13 FOREIGN KEY (comment_id) REFERENCES comments (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA7457294869C');
        $this->addSql('ALTER TABLE reports DROP FOREIGN KEY FK_F11FA745F8697D13');
        $this->addSql('ALTER TABLE reports CHANGE status status VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT `FK_F11FA7457294869C` FOREIGN KEY (article_id) REFERENCES articles (id)');
        $this->addSql('ALTER TABLE reports ADD CONSTRAINT `FK_F11FA745F8697D13` FOREIGN KEY (comment_id) REFERENCES comments (id)');
    }
}
