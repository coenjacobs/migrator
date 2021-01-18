<?php

namespace CoenJacobs\Migrator\Loggers;

use CoenJacobs\Migrator\Contracts\Logger;
use CoenJacobs\Migrator\Contracts\Worker;

abstract class BaseLogger implements Logger
{
    /**
     * @var Worker
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected $worker;

    public function setWorker(Worker $worker): void
    {
        $this->worker = $worker;
    }
}
