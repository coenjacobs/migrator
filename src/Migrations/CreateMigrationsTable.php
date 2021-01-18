<?php

namespace CoenJacobs\Migrator\Migrations;

class CreateMigrationsTable extends BaseMigration
{
    public static function id(): string
    {
        return 'migrator-1-migrations-table';
    }

    public function up(): void
    {
        $query = "CREATE TABLE $this->tableName (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            plugin_key VARCHAR(255) NOT NULL,
            batch BIGINT UNSIGNED )";

        $this->worker->query($query);
    }

    public function down(): void
    {
        $query = "DROP TABLE $this->tableName";
        $this->worker->query($query);
    }
}
