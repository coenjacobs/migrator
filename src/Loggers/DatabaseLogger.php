<?php

namespace CoenJacobs\Migrator\Loggers;

use CoenJacobs\Migrator\Contracts\Migration;
use CoenJacobs\Migrator\Migrations\CreateMigrationsTable;

class DatabaseLogger extends BaseLogger
{
    /** @var string */
    protected $tableName;

    /** @var bool */
    protected $setup = false;

    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
    }

    public function init(): void
    {
        if (!$this->isTableSetup()) {
            $migration = new CreateMigrationsTable($this->worker);
            $migration->setTableName($this->tableName);
            $migration->up();

            $this->setup = true;
        }
    }

    public function add(string $plugin_key, Migration $migration, int $batch)
    {
        $this->init();
        $id = $migration->id();

        $batch = intval($batch);

        $query = "INSERT INTO $this->tableName (migration, plugin_key, batch)
                  VALUES ('$id', '$plugin_key', '$batch')";
        $this->worker->query($query);
    }

    public function remove(string $plugin_key, Migration $migration)
    {
        $this->init();
        $id = $migration->id();
        $query = "DELETE FROM $this->tableName (migration, plugin_key)
                  VALUES ('$id', '$plugin_key')";
        $this->worker->query($query);
    }

    public function getLoggedMigrations(array $plugin_keys): array
    {
        $this->init();

        $query = 'SELECT migration FROM ' . $this->tableName . '
                  WHERE plugin_key IN ("' . implode('","', $plugin_keys) . '")';

        $results = $this->worker->getResults($query);

        $migrations = [];

        foreach ($results as $result) {
            $migrations[] = $result->migration;
        }

        return $migrations;
    }

    public function getHighestBatchNumber(): int
    {
        $this->init();

        $query = 'SELECT MAX(batch) AS batch FROM ' . $this->tableName . ';';
        $results = $this->worker->getResults($query);

        if (empty($results)) {
            return 0;
        }

        return (int) array_pop($results)->batch;
    }

    protected function isTableSetup(): bool
    {
        if ($this->setup === true) {
            return true;
        }

        $databaseName = $this->worker->getDatabaseName();

        // Check if table exists before we try to query it
        $query = "SELECT count(*)
                  FROM information_schema.TABLES
                  WHERE (TABLE_SCHEMA = '$databaseName') AND (TABLE_NAME = '$this->tableName')";

        $result = $this->worker->getResults($query);

        if (empty($result) || $result[0]->{"count(*)"} == 0) {
            return false;
        }

        $this->setup = true;
        return true;
    }
}
