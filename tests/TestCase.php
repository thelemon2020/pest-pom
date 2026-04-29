<?php

declare(strict_types=1);

namespace Thelemon2020\PestPages\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Thelemon2020\PestPages\PestPagesServiceProvider;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [PestPagesServiceProvider::class];
    }
}