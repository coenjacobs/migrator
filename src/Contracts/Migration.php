<?php

namespace CoenJacobs\Migrator\Contracts;

interface Migration
{
    public static function id(): string;
    public function up(): void;
    public function down(): void;
}
