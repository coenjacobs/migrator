<?php

namespace CoenJacobs\MigratorTests\Unit;

use CoenJacobs\Migrator\Contracts\Migration as MigrationContract;
use CoenJacobs\Migrator\Handler;
use CoenJacobs\Migrator\Loggers\BaseLogger;
use CoenJacobs\Migrator\Migrations\BaseMigration;
use CoenJacobs\Migrator\Workers\BaseWorker;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{
    /** @test */
    public function callsUpOnMigration()
    {
        $this->expectExceptionMessage('up method called');

        $handler = new Handler(new Worker(), new Logger());
        $handler->add('test-up-migrations', Migration::class);
        $handler->up('test-up-migrations');
    }

    /** @test */
    public function callsDownOnMigration()
    {
        $this->expectExceptionMessage('down method called');

        $handler = new Handler(new Worker(), new Logger());
        $handler->add('test-down-migrations', Migration::class);
        $handler->down('test-down-migrations');
    }
}

class Logger extends BaseLogger
{
    public function add($plugin_key, MigrationContract $migration, $batch)
    {
    }
    public function remove($plugin_key, MigrationContract $migration)
    {
    }

    public function getLoggedMigrations(array $plugin_keys): array
    {
        if (in_array('test-down-migrations', $plugin_keys)) {
            return ['test-migration'];
        } else {
            return [];
        }
    }

    public function getHighestBatchNumber(): int
    {
        return 1;
    }
}

class Worker extends BaseWorker
{
    public function getPrefix(): string
    {
    }
    public function getDatabaseName(): string
    {
    }
    public function query($query)
    {
    }
    public function getResults($query)
    {
    }
}

class Migration extends BaseMigration
{
    public static function id(): string
    {
        return 'test-migration';
    }

    /**
     * @throws \Exception
     */
    public function up(): void
    {
        throw new \Exception('up method called');
    }

    /**
     * @throws \Exception
     */
    public function down(): void
    {
        throw new \Exception('down method called');
    }
}
