<?php

namespace CoenJacobs\Migrator;

use CoenJacobs\Migrator\Contracts\Logger;
use CoenJacobs\Migrator\Contracts\Worker;
use CoenJacobs\Migrator\Contracts\Migration;
use CoenJacobs\Migrator\Exceptions\ReservedNameException;
use CoenJacobs\Migrator\Migrations\CreateMigrationsTable;

class Handler
{
    /** @var Worker */
    protected $worker;

    /** @var Logger */
    protected $logger;

    /** @var array */
    protected $migrations = [];

    /** @var array */
    protected $reservedNames = [];

    /**
     * @param Worker $worker
     * @param Logger $logger
     */
    public function __construct(Worker $worker, Logger $logger)
    {
        $this->worker = $worker;
        $this->logger = $logger;
        $this->logger->setWorker($this->worker);

        $this->setupCoreMigrations();
    }

    private function setupCoreMigrations()
    {
        $this->add('core', CreateMigrationsTable::class);

        // From here on, the 'core' index as $plugin_key is a reserved name and
        // can't be used by anyone else.
        $this->reservedNames = [
            'core'
        ];
    }

    /**
     * @param string $pluginKey
     * @return bool
     */
    public function isReservedName($pluginKey)
    {
        return in_array($pluginKey, $this->reservedNames);
    }

    /**
     * @param string $pluginKey
     * @param $migrationClassName
     * @throws ReservedNameException
     */
    public function add($pluginKey, $migrationClassName)
    {
        if ($this->isReservedName($pluginKey)) {
            throw new ReservedNameException($pluginKey . ' is a reserved name and can not be used by implementations.');
        }

        $this->migrations[ $pluginKey ][] = $migrationClassName;
    }

    /**
     * Run up() method on all migrations that have not already been run for this $pluginKey.
     * Core migrations will always be run first to setup base tables for logging.
     *
     * @param string $pluginKey
     * @throws ReservedNameException
     */
    public function up($pluginKey)
    {
        if ($this->isReservedName($pluginKey)) {
            throw new ReservedNameException($pluginKey . ' is a reserved name and can not be used by implementations.');
        }

        if (! isset($this->migrations[ $pluginKey ])) {
            return;
        }

        $runMigrations = $this->logger->getLoggedMigrations(['core', $pluginKey]);

        $migrationsToRun = [];

        // Add core migrations first
        foreach ($this->migrations[ 'core' ] as $migrationClass) {
            if (! in_array($migrationClass::id(), $runMigrations)) {
                $migrationsToRun['core'][] = new $migrationClass($this->worker);
            }
        }

        // Add added migrations for $pluginKey second
        foreach ($this->migrations[ $pluginKey ] as $migrationClass) {
            if (! in_array($migrationClass::id(), $runMigrations)) {
                $migrationsToRun[ $pluginKey ][] = new $migrationClass($this->worker);
            }
        }

        $this->upAction($migrationsToRun);
    }

    /**
     * Run down() method on all migrations that have already run up() for this $pluginKey.
     * Core migrations will not be reversed since they can still be used by another plugin.
     *
     * @param string $pluginKey
     * @throws ReservedNameException
     */
    public function down($pluginKey)
    {
        if ($this->isReservedName($pluginKey)) {
            throw new ReservedNameException($pluginKey . ' is a reserved name and can not be used by implementations.');
        }

        if (! isset($this->migrations[ $pluginKey ])) {
            return;
        }

        $runMigrations = $this->logger->getLoggedMigrations([$pluginKey]);

        $migrationsToRun = [];

        // Add added migrations for $pluginKey second
        foreach ($this->migrations[ $pluginKey ] as $migrationClass) {
            if (in_array($migrationClass::id(), $runMigrations)) {
                $migrationsToRun[ $pluginKey ][] = new $migrationClass($this->worker);
            }
        }

        // Flip the array, so they are executed in reverse order as they were run in $this->up()
        $migrationsToRun = array_reverse($migrationsToRun);

        $this->downAction($migrationsToRun);
    }

    protected function upAction($migrationsToRun)
    {
        $batch = $this->logger->getHighestBatchNumber() + 1;

        foreach ($migrationsToRun as $key => $migrations) {
            foreach ($migrations as $migration) {
                /** @var $migration Migration */
                $migration->up();
                $this->logger->add($key, $migration, $batch);
            }
        }
    }

    protected function downAction($migrationsToRun)
    {
        foreach ($migrationsToRun as $key => $migrations) {
            foreach ($migrations as $migration) {
                /** @var $migration Migration */
                $migration->down();
                $this->logger->remove($key, $migration);
            }
        }
    }
}
