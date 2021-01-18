<?php

namespace CoenJacobs\Migrator\Contracts;

interface Worker
{
    public function getPrefix(): string;
    public function getDatabaseName(): string;
    public function query(string $query): int;
    public function getResults(string $query): array;
}
