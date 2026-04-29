<?php

declare(strict_types=1);

namespace Thelemon2020\PestPages\Tests\Fixtures;

use Thelemon2020\PestPages\Page;

class ExamplePage extends Page
{
    public static function url(): string
    {
        return '/example';
    }
}