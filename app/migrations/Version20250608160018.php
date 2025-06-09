<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250608160018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE payment_transactions (id VARCHAR(36) NOT NULL, amount_in_minor_units INT NOT NULL, currency VARCHAR(3) NOT NULL, last4_digits VARCHAR(4) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, gateway VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, external_transaction_id VARCHAR(255) DEFAULT NULL, metadata JSON DEFAULT NULL, PRIMARY KEY(id))
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE payment_transactions
        SQL);
    }
}
