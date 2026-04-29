<?php

declare(strict_types=1);

use Thelemon2020\PestPages\Config;

it('returns the default path when no override is set', function () {
    expect(Config::path())->toBe('tests/Browser/Pages');
});

it('returns a custom path from the config', function () {
    config(['pest-pages.path' => 'tests/Custom/Pages']);

    expect(Config::path())->toBe('tests/Custom/Pages');
});

it('absolutePath resolves a relative path against the project root', function () {
    config(['pest-pages.path' => 'tests/Browser/Pages']);

    expect(Config::absolutePath())
        ->toStartWith(base_path())
        ->toEndWith('tests'.DIRECTORY_SEPARATOR.'Browser'.DIRECTORY_SEPARATOR.'Pages');
});

it('absolutePath returns an absolute path unchanged', function () {
    $absolute = '/some/absolute/path';
    config(['pest-pages.path' => $absolute]);

    expect(Config::absolutePath())->toBe($absolute);
});