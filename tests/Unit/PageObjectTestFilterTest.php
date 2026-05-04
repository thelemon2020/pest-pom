<?php

declare(strict_types=1);

use Thelemon2020\PestPom\Filters\PageObjectTestFilter;

it('returns false for code with no page helper calls', function () {
    $filter = new PageObjectTestFilter();

    expect($filter->containsPageObjectCall('$x = 1 + 1;'))->toBeFalse();
});

it('detects a bare page() function call', function () {
    $filter = new PageObjectTestFilter();

    expect($filter->containsPageObjectCall(' page(SomePage::class);'))->toBeTrue();
});

it('detects a Page::open() static call', function () {
    $filter = new PageObjectTestFilter();

    expect($filter->containsPageObjectCall('SomePage::open();'))->toBeTrue();
});

it('does not detect page() called as an object method', function () {
    $filter = new PageObjectTestFilter();

    expect($filter->containsPageObjectCall('$browser->page(SomePage::class);'))->toBeFalse();
});

it('does not detect open() called as an object method', function () {
    $filter = new PageObjectTestFilter();

    expect($filter->containsPageObjectCall('$browser->open();'))->toBeFalse();
});

it('does not detect page() inside a string literal', function () {
    $filter = new PageObjectTestFilter();

    expect($filter->containsPageObjectCall('$x = \'page(SomePage::class)\';'))->toBeFalse();
});

it('does not detect page() inside a comment', function () {
    $filter = new PageObjectTestFilter();

    expect($filter->containsPageObjectCall('// page(SomePage::class)'."\n".'$x = 1;'))->toBeFalse();
});