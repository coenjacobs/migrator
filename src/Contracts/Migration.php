<?php

namespace CoenJacobs\Migrator\Contracts;

interface Migration
{
    public function getId();
    public function up();
    public function down();
}
