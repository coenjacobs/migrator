<?php

namespace CoenJacobs\Migrator\Contracts;

interface Logger
{
    public function add($plugin_key, Migration $migration, $batch);
    public function remove($plugin_key, Migration $migration);
    public function setWorker(Worker $worker);
    public function getLoggedMigrations($plugin_keys);
    public function getHighestBatchNumber();
}
