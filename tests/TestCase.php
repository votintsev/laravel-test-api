<?php

namespace Votintsev\PublicSeeding\Tests;

use Votintsev\PublicSeeding\PublicSeedingServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [PublicSeedingServiceProvider::class];
    }
}