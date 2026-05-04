<?php

declare(strict_types=1);

use Pest\Expectation;
use Thelemon2020\PestPom\Page;

expect()->extend('toBeOnPage', function (string $pageClass): Expectation {
    /** @var Page $page */
    $page = $this->value;
    $page->assertPathIs($pageClass::url());

    return $this;
});

expect()->extend('toSee', function (string $text): Expectation {
    /** @var Page $page */
    $page = $this->value;
    $page->assertSee($text);

    return $this;
});