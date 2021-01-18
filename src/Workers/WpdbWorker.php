<?php

namespace CoenJacobs\Migrator\Workers;

class WpdbWorker extends BaseWorker
{
    /** @var */
    protected $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function getPrefix(): string
    {
        return $this->wpdb->prefix;
    }

    public function getDatabaseName(): string
    {
        return $this->wpdb->dbname;
    }

    public function query(string $query)
    {
        return $this->wpdb->query($query);
    }

    public function getResults(string $query)
    {
        return $this->wpdb->get_results($query);
    }
}
