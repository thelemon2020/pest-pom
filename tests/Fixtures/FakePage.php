<?php

declare(strict_types=1);

namespace Thelemon2020\PestPom\Tests\Fixtures;

use Thelemon2020\PestPom\Page;

/**
 * A Page subclass for unit tests. Records every __call invocation instead of
 * delegating to a real browser, so Concerns and Expectations can be tested
 * without a live Playwright connection.
 */
class FakePage extends Page
{
    /** @var array<int, array{method: string, args: array<int, mixed>}> */
    public array $calls = [];

    public static function url(): string
    {
        return '/fake';
    }

    public function __call(string $method, array $args): mixed
    {
        $this->calls[] = ['method' => $method, 'args' => $args];

        return $this;
    }
}