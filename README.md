# Migrator [![Build Status](https://api.travis-ci.org/coenjacobs/migrator.png)](https://travis-ci.org/coenjacobs/migrator) [![Latest Stable Version](https://poser.pugx.org/coenjacobs/migrator/v/stable.svg)](https://packagist.org/packages/coenjacobs/migrator) [![License](https://poser.pugx.org/coenjacobs/migrator/license.svg)](https://packagist.org/packages/coenjacobs/migrator)

Migrator allows you to run migrations on your database, for your WordPress plugin specific needs. This can be to either add or remove tables, change the structure of the tables, or change the data being contained in your tables.

This package requires PHP 5.6 or higher in order to run the tool.

**Warning:** This package is very experimental and breaking changes are very likely until version 1.0.0 is tagged. Use with caution, always wear a helmet when using this in production environments.

## Installation

This package can be best installed inside your plugin, by using Composer:

`composer require coenjacobs/migrator`

Best results are achieved when installing this library in combination with the [Mozart package](https://github.com/coenjacobs/mozart), so Migrator will be installed in your own namespace to prevent conflicts.

## Workers

Worker classes are the classes responsible for actually performing the queries your migrations want to run. By default, a `$wpdb` based Worker is available, in the `CoenJacobs\Migrator\Workers\WpdbWorker` class. This utilises the default `$wpdb` global variable in WordPress installations, to run your queries. You can provide your own implementation of the Worker class, as long as they implement the `CoenJacobs\Migrator\Contracts\Worker` interface.

## Loggers

Loggers take care of registering the migrations that have been run already. This is to ensure that no migrations are being run more than once. By default, there is a database based Logger available, in the `CoenJacobs\Migrator\Loggers\DatabaseLogger` class. This class actually uses the aforementioned `$wpdb` based Worker, in order to log the migration data into a specific database table. You can provide your own implementation of the Logger class, as long as they implement the `CoenJacobs\Migrator\Contracts\Logger` interface.

## Migration structure

All of the migrations are required to follow a specific format, being enforced by the `CoenJacobs\Migrator\Contracts\Migration.php` interface. This interface enforces your migration class to contain at least two methods: `up()` and `down()`. These two methods are used to run and rollback your migration.

There is a helper `BaseMigration` available, to help with setting up the right variables for the migration, such as the Worker.

A basic migration example looks like this:

```php
<?php

use CoenJacobs\Migrator\Migrations\BaseMigration;

namespace YourPlugin\Migrations;

class CreateTestTable extends BaseMigration
{
    public function getId()
    {
        return 'yourplugin-1-test-table';
    }

    public function up()
    {
        $tableName = $this->worker->getPrefix() . 'yourplugin_test';

        $query = "CREATE TABLE $tableName (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            testvarchar VARCHAR(255) NOT NULL )";

        $this->worker->query($query);
    }

    public function down()
    {
        $tableName = $this->worker->getPrefix() . 'yourplugin_test';

        $query = "DROP TABLE $tableName";
        $this->worker->query($query);
    }
}
```

## Register migrations

In order for this library to run your migrations, the migrations need to be added to the Handler. The Handler is the core class of this library, which takes care of running your migrations in the right order.

The Handler needs to be provided with a Worker and a Logger, in order to set it up:

```php
use CoenJacobs\Migrator\Handler;
use CoenJacobs\Migrator\Loggers\DatabaseLogger;
use CoenJacobs\Migrator\Workers\WpdbWorker;

$worker = new WpdbWorker();
$migrator = new Handler($worker, new DatabaseLogger());
```

After that, the Handler is ready to accept new migrations to be added, before they can be run. Each Migration needs to be provided with a Worker class, again implementing the `CoenJacobs\Migrator\Contracts\Worker` interface, which is responsible for running the queries inside your Migration. You can pass a different Worker class to your Migration, than the one you have passed to the Handler, but you can also use the same:

```php
use YourPlugin\Migrations\CreateTestTable

$migrator->add('yourplugin', new CreateTestTable($worker));
```

The first parameter in the `$migration->add()` method should be a unique identifier for your plugin. The handler runs the migrations on a per plugin basis, using this unique identifier as the key to call the right migrations to be run. 

## Running migrations

Running migrations, after they have been setup, is as easy as running the `$migration->up()` method with the first parameter being the unique identifier for your plugin:

```php
$migrator->up('yourplugin');
```

This will run all the migrations being added using the unique identifier, except for the ones that have already been run. The Logger functionality takes care of never running the same migration more than once.