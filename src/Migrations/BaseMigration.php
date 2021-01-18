<?php

namespace CoenJacobs\Migrator\Migrations;

use CoenJacobs\Migrator\Contracts\Worker;
use CoenJacobs\Migrator\Contracts\Migration;

abstract class BaseMigration implements Migration
{
    /**
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected $tableName;

    /** @var Worker */
    protected $worker;

    public function __construct(Worker $worker)
    {
        $this->worker = $worker;
    }

    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }
}
