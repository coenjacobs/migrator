<?php

namespace CoenJacobs\Migrator;

use CoenJacobs\Migrator\Contracts\Logger;
use CoenJacobs\Migrator\Contracts\Worker;
use CoenJacobs\Migrator\Contracts\Migration;

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
    }

    /**
     * @param string $pluginKey
     * @param string $migrationClassName
     */
    public function add(string $pluginKey, string $migrationClassName): void
    {
        $this->migrations[ $pluginKey ][] = $migrationClassName;
    }

    /**
     * Run up() method on all migrations that have not already been run for this $pluginKey.
     * Core migrations will always be run first to setup base tables for logging.
     *
     * @param string $pluginKey
     */
    public function up(string $pluginKey): void
    {
        if (! isset($this->migrations[ $pluginKey ])) {
            return;
        }

        $runMigrations = $this->logger->getLoggedMigrations([$pluginKey]);

        $migrationsToRun = [];

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
     */
    public function down(string $pluginKey): void
    {
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

    /**
     * @param array $migrationsToRun
     */
    protected function upAction(array $migrationsToRun): void
    {
        $batch = $this->logger->getHighestBatchNumber() + 1;

        foreach ($migrationsToRun as $key => $migrations) {
            foreach ($migrations as $migration) {
                /** @var Migration $migration */
                $migration->up();
                $this->logger->add($key, $migration, $batch);
            }
        }
    }

    /**
     * @param array $migrationsToRun
     */
    protected function downAction(array $migrationsToRun): void
    {
        foreach ($migrationsToRun as $key => $migrations) {
            foreach ($migrations as $migration) {
                /** @var Migration $migration */
                $migration->down();
                $this->logger->remove($key, $migration);
            }
        }
    }
}
