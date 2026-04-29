<?php

declare(strict_types=1);

namespace Thelemon2020\PestPom\Tests\Fixtures;

use Thelemon2020\PestPom\Component;

class ExampleComponent extends Component
{
    public static function selector(): string
    {
        return '#example';
    }
}
