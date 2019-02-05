<?php

namespace CoenJacobs\Migrator\Migrations;

class CreateMigrationsTable extends BaseMigration
{
    /** @var string */
    protected $tableName;

    public static function id()
    {
        return 'migrator-1-migrations-table';
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    public function up()
    {
        $query = "CREATE TABLE $this->tableName (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            plugin_key VARCHAR(255) NOT NULL,
            batch BIGINT UNSIGNED )";

        $this->worker->query($query);
    }

    public function down()
    {
        $query = "DROP TABLE $this->tableName";
        $this->worker->query($query);
    }
}
