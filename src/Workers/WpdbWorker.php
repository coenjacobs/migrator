<?php

namespace CoenJacobs\Migrator\Workers;

class WpdbWorker extends BaseWorker
{
    /** @var mixed */
    protected $wpdb;

    public function __construct()
    {
        /**
         * @var mixed
         * @psalm-suppress MissingPropertyType
         */
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

    public function query(string $query): int
    {
        $result = $this->wpdb->query($query);

        if (is_bool($result)) {
            return $result ? 1 : 0;
        }

        return (int) $result;
    }

    public function getResults(string $query): array
    {
        return $this->wpdb->get_results($query);
    }
}
