<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Populate authorName field with existing author names
 */
final class Version20260109110200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Populate authorName field with existing author names';
    }

    public function up(Schema $schema): void
    {
        // Update all articles with author_id to set author_name from users table
        $this->addSql('UPDATE article a 
                      INNER JOIN users u ON a.author_id = u.id 
                      SET a.author_name = u.name 
                      WHERE a.author_name IS NULL');
    }

    public function down(Schema $schema): void
    {
        // This migration is data-related, down would clear the data
        $this->addSql('UPDATE article SET author_name = NULL');
    }
}
