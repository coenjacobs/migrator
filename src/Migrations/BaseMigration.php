<?php

namespace CoenJacobs\Migrator\Migrations;

use CoenJacobs\Migrator\Contracts\Worker;
use CoenJacobs\Migrator\Contracts\Migration;

abstract class BaseMigration implements Migration
{
    /** @var Worker */
    protected $worker;

    public function __construct(Worker $worker)
    {
        $this->worker = $worker;
    }
}
