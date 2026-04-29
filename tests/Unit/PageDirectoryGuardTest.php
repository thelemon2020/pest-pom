<?php

declare(strict_types=1);

use Thelemon2020\PestPages\Config;
use Thelemon2020\PestPages\Tests\Fixtures\ExamplePage;

it('does not throw when a page class lives in the configured directory', function () {
    $fixturesDir = dirname((new ReflectionClass(ExamplePage::class))->getFileName());
    config(['pest-pages.path' => $fixturesDir]);

    Config::assertPageIsInConfiguredDirectory(ExamplePage::class);

    expect(true)->toBeTrue();
});

it('throws when a page class lives outside the configured directory', function () {
    // Default path points to tests/Browser/Pages — ExamplePage is in tests/Fixtures
    Config::assertPageIsInConfiguredDirectory(ExamplePage::class);
})->throws(RuntimeException::class, 'configured pages directory');

it('includes the class name in the exception message', function () {
    try {
        Config::assertPageIsInConfiguredDirectory(ExamplePage::class);
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toContain(ExamplePage::class);
    }
});

it('includes the configured path in the exception message', function () {
    try {
        Config::assertPageIsInConfiguredDirectory(ExamplePage::class);
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toContain(Config::absolutePath());
    }
});