<?php

namespace CoenJacobs\Migrator\Contracts;

interface Worker
{
    public function getPrefix();
    public function getDatabaseName();
    public function query($query);
    public function getResults($query);
}
