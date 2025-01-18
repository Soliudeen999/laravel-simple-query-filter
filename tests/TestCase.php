<?php

namespace Soliudeen999\QueryFilter\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getEnvironmentSetUp($app)
    {
        // Use SQLite in memory
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            'Soliudeen999\QueryFilter\Providers\QueryFilterServiceProvider',
        ];
    }
}
