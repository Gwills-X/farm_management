<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Safety guard: refuse to run tests against any database whose name does not end in _test.
     * This runs before RefreshDatabase, so the real farm database is never wiped.
     */
    public function createApplication()
    {
        $app = parent::createApplication();

        $database = (string) $app['config']->get('database.connections.mysql.database');

        if (! str_ends_with($database, '_test')) {
            throw new RuntimeException(
                "Refusing to run tests against database '{$database}'. Use a database name ending in _test."
            );
        }

        return $app;
    }
}
