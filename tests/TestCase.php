<?php

namespace Votintsev\TestApi\Tests;

use Votintsev\TestApi\TestApiServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [TestApiServiceProvider::class];
    }
}