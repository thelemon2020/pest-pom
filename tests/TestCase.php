<?php

declare(strict_types=1);

namespace Thelemon2020\PestPom\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Thelemon2020\PestPom\PestPomServiceProvider;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [PestPomServiceProvider::class];
    }
}