<?php

namespace CoenJacobs\MigratorTests\Unit;

use CoenJacobs\Migrator\Contracts\Migration as MigrationContract;
use CoenJacobs\Migrator\Handler;
use CoenJacobs\Migrator\Loggers\BaseLogger;
use CoenJacobs\Migrator\Migrations\BaseMigration;
use CoenJacobs\Migrator\Workers\BaseWorker;
use PHPUnit_Framework_TestCase;

class HandlerTest extends PHPUnit_Framework_TestCase
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
    public function add($plugin_key, MigrationContract $migration, $batch) { }
    public function remove($plugin_key, MigrationContract $migration) { }

    public function getLoggedMigrations($plugin_keys)
    {
        if (in_array('test-down-migrations', $plugin_keys)) {
            return ['test-migration'];
        } else {
            return [];
        }
    }

    public function getHighestBatchNumber() { }
}

class Worker extends BaseWorker
{
    public function getPrefix() { }
    public function getDatabaseName() { }
    public function query($query) { }
    public function getResults($query) { }
}

class Migration extends BaseMigration
{
    public static function id()
    {
        return 'test-migration';
    }

    /**
     * @throws \Exception
     */
    public function up()
    {
        throw new \Exception('up method called');
    }

    /**
     * @throws \Exception
     */
    public function down()
    {
        throw new \Exception('down method called');
    }
}