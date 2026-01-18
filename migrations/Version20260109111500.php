<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ensure all articles have author_name populated
 */
final class Version20260109111500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Populate all remaining articles with author names';
    }

    public function up(Schema $schema): void
    {
        // Update all articles where author_name is NULL but author_id exists
        $this->addSql('UPDATE article a 
                      INNER JOIN users u ON a.author_id = u.id 
                      SET a.author_name = u.name 
                      WHERE a.author_name IS NULL AND a.author_id IS NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // This migration is data-related
        $this->addSql('UPDATE article SET author_name = NULL WHERE author_name IS NOT NULL AND author_id IS NOT NULL');
    }
}
