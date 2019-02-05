<?php

namespace CoenJacobs\MigratorTests\Unit;

use CoenJacobs\Migrator\Handler;
use CoenJacobs\Migrator\Contracts\Logger;
use CoenJacobs\Migrator\Contracts\Worker;
use CoenJacobs\Migrator\Contracts\Migration;
use PHPUnit_Framework_TestCase;

class HandlerTest extends PHPUnit_Framework_TestCase
{
    private $worker;
    private $logger;
    private $downLogger;
    private $migration;

    public function setUp()
    {
        $this->worker = $this->getMockBuilder(Worker::class)
            ->getMock();

        $this->logger = $this->getMockBuilder(Logger::class)
            ->getMock();
        $this->logger->expects($this->any())
            ->method('getLoggedMigrations')
            ->will($this->returnValue([]));

        $this->downLogger = $this->getMockBuilder(Logger::class)
            ->getMock();
        $this->downLogger->expects($this->any())
            ->method('getLoggedMigrations')
            ->will($this->returnValue(['test-migration']));

        $this->migration = $this->getMockBuilder(Migration::class)
            ->getMock();
        $this->migration->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('test-migration'));
    }

    /** @test */
    public function testHandlerCallsUpOnMigration()
    {
        $this->migration->expects($this->once())->method('up');

        $handler = new Handler($this->worker, $this->logger);
        $handler->add('test', $this->migration);
        $handler->up('test');
    }

    /** @test */
    public function testHandlerCallsDownOnMigration()
    {
        $this->migration->expects($this->once())->method('down');

        $handler = new Handler($this->worker, $this->downLogger);
        $handler->add('test', $this->migration);
        $handler->down('test');
    }

    /** @test */
    public function testHandlerDoesntCallDifferentPluginMigrations()
    {
        $this->migration->expects($this->never())->method($this->anything());

        $handler = new Handler($this->worker, $this->logger);
        $handler->add('another-key', $this->migration);
        $handler->up('test');
    }
}
