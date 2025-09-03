<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250903122321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tasks table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE tasks (
                            id SERIAL NOT NULL,
                            title VARCHAR(255) NOT NULL,
                            description TEXT DEFAULT NULL,
                            status VARCHAR(50) NOT NULL,
                            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                            created_by INT DEFAULT NULL,
                            updated_by INT DEFAULT NULL,
                            PRIMARY KEY(id))
                        ');
        $this->addSql('CREATE INDEX idx_task_status ON tasks (status)');
        $this->addSql('CREATE INDEX idx_task_created_at ON tasks (created_at)');
        $this->addSql('COMMENT ON COLUMN tasks.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN tasks.updated_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE tasks');
    }
}
