<?php

namespace CoenJacobs\Migrator\Contracts;

interface Logger
{
    public function add(string $plugin_key, Migration $migration, int $batch);
    public function remove(string $plugin_key, Migration $migration);
    public function setWorker(Worker $worker): void;
    public function getLoggedMigrations(array $plugin_keys): array;
    public function getHighestBatchNumber(): int;
}
