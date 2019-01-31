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

    public function getPrefix()
    {
        return $this->wpdb->prefix;
    }

    public function getDatabaseName()
    {
        return $this->wpdb->dbname;
    }

    public function query($query)
    {
        return $this->wpdb->query($query);
    }

    public function getResults($query)
    {
        return $this->wpdb->get_results($query);
    }
}
