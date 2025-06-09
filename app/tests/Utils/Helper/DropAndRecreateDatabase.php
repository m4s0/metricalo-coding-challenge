<?php

declare(strict_types=1);

namespace App\Tests\Utils\Helper;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

class DropAndRecreateDatabase
{
    public static function execute(EntityManagerInterface $entityManager): void
    {
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

        if (empty($metadata)) {
            return;
        }

        try {
            $tool = new SchemaTool($entityManager);

            $connection = $entityManager->getConnection();
            $connection->executeQuery('DROP SCHEMA public CASCADE');
            $connection->executeQuery('CREATE SCHEMA public');
            $connection->executeQuery('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
            $connection->close();

            $tool->createSchema($metadata);
        } catch (Exception $e) {
            throw new \RuntimeException('Failed to drop and recreate the database schema: '.$e->getMessage());
        }
    }
}
