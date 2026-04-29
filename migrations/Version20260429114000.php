<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260429114000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create products table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE products (
                id UUID NOT NULL,
                name VARCHAR(255) NOT NULL,
                price NUMERIC(10, 2) NOT NULL,
                quantity INT NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql("COMMENT ON COLUMN products.id IS '(DC2Type:uuid)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE products');
    }
}
