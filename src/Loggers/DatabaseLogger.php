<?php

namespace CoenJacobs\Migrator\Loggers;

use CoenJacobs\Migrator\Contracts\Migration;

class DatabaseLogger extends BaseLogger
{
    public function add($plugin_key, Migration $migration, $batch)
    {
        $id = $migration->getId();
        $tableName = $this->worker->getPrefix() . 'migrator_migrations';

        $batch = intval($batch);

        $query = "INSERT INTO $tableName (migration, plugin_key, batch)
                  VALUES ('$id', '$plugin_key', '$batch')";
        $this->worker->query($query);
    }

    public function remove($plugin_key, Migration $migration)
    {
        $id = $migration->getId();
        $tableName = $this->worker->getPrefix() . 'migrator_migrations';
        $query = "DELETE FROM $tableName (migration, plugin_key)
                  VALUES ('$id', '$plugin_key')";
        $this->worker->query($query);
    }

    public function getLoggedMigrations($plugin_keys)
    {
        $databaseName = $this->worker->getDatabaseName();
        $tableName = $this->worker->getPrefix() . 'migrator_migrations';

        // Check if table exists before we try to query it
        $query = "SELECT count(*)
                  FROM information_schema.TABLES
                  WHERE (TABLE_SCHEMA = '$databaseName') AND (TABLE_NAME = '$tableName')";

        $result = $this->worker->getResults($query);

        if (empty($result) || $result[0]->{"count(*)"} == 0) {
            return [];
        }

        $query = 'SELECT migration FROM '.$tableName.'
                  WHERE plugin_key IN ("'. implode('","', $plugin_keys) .'")';

        $results = $this->worker->getResults($query);

        $migrations = [];

        foreach ($results as $result) {
            $migrations[] = $result->migration;
        }
        return $migrations;
    }

    public function getHighestBatchNumber()
    {
        $databaseName = $this->worker->getDatabaseName();
        $tableName = $this->worker->getPrefix() . 'migrator_migrations';

        // Check if table exists before we try to query it
        $query = "SELECT count(*)
                  FROM information_schema.TABLES
                  WHERE (TABLE_SCHEMA = '$databaseName') AND (TABLE_NAME = '$tableName')";

        $result = $this->worker->getResults($query);

        if (empty($result) || $result[0]->{"count(*)"} == 0) {
            return 0;
        }

        $tableName = $this->worker->getPrefix() . 'migrator_migrations';
        $query = 'SELECT MAX(batch) AS batch FROM '.$tableName.';';
        $results = $this->worker->getResults($query);

        if (empty($results)) {
            return 0;
        }

        return array_pop($results)->batch;
    }
}
