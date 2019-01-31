<?php

namespace CoenJacobs\Migrator\Migrations;

class CreateMigrationsTable extends BaseMigration
{
    public function getId()
    {
        return 'migrator-1-migrations-table';
    }

    public function up()
    {
        $tableName = $this->worker->getPrefix() . 'migrator_migrations';

        $query = "CREATE TABLE $tableName (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            plugin_key VARCHAR(255) NOT NULL,
            batch BIGINT UNSIGNED )";

        $this->worker->query($query);
    }

    public function down()
    {
        $tableName = $this->worker->getPrefix() . 'migrator_migrations';

        $query = "DROP TABLE $tableName";
        $this->worker->query($query);
    }
}
