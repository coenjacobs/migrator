<?php

namespace CoenJacobs\Migrator\Loggers;

use CoenJacobs\Migrator\Contracts\Migration;
use CoenJacobs\Migrator\Migrations\CreateMigrationsTable;

class DatabaseLogger extends BaseLogger
{
    /** @var string */
    protected $tableName;

    public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }

    public function init()
    {
        if (!$this->isTableSetup()) {
            $migration = new CreateMigrationsTable($this->worker);
            $migration->setTableName($this->tableName);
            $migration->up();
        }
    }

    public function isTableSetup()
    {
        $databaseName = $this->worker->getDatabaseName();

        // Check if table exists before we try to query it
        $query = "SELECT count(*)
                  FROM information_schema.TABLES
                  WHERE (TABLE_SCHEMA = '$databaseName') AND (TABLE_NAME = '$this->tableName')";

        $result = $this->worker->getResults($query);

        if (empty($result) || $result[0]->{"count(*)"} == 0) {
            return false;
        }

        return true;
    }

    public function add($plugin_key, Migration $migration, $batch)
    {
        $this->init();
        $id = $migration->id();

        $batch = intval($batch);

        $query = "INSERT INTO $this->tableName (migration, plugin_key, batch)
                  VALUES ('$id', '$plugin_key', '$batch')";
        $this->worker->query($query);
    }

    public function remove($plugin_key, Migration $migration)
    {
        $this->init();
        $id = $migration->id();
        $query = "DELETE FROM $this->tableName (migration, plugin_key)
                  VALUES ('$id', '$plugin_key')";
        $this->worker->query($query);
    }

    public function getLoggedMigrations($plugin_keys)
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

    public function getHighestBatchNumber()
    {
        $this->init();

        $query = 'SELECT MAX(batch) AS batch FROM ' . $this->tableName . ';';
        $results = $this->worker->getResults($query);

        if (empty($results)) {
            return 0;
        }

        return array_pop($results)->batch;
    }
}
