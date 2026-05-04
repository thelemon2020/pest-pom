<?php

declare(strict_types=1);

use Pest\Expectation;
use Thelemon2020\PestPom\Tests\Fixtures\ExamplePage;
use Thelemon2020\PestPom\Tests\Fixtures\FakePage;

it('toBeOnPage calls assertPathIs with the page url', function () {
    $page = new FakePage(pendingBrowser());

    expect($page)->toBeOnPage(ExamplePage::class);

    expect($page->calls)->toContain(['method' => 'assertPathIs', 'args' => [ExamplePage::url()]]);
});

it('toSee calls assertSee with the given text', function () {
    $page = new FakePage(pendingBrowser());

    expect($page)->toSee('Welcome back');

    expect($page->calls)->toContain(['method' => 'assertSee', 'args' => ['Welcome back']]);
});

it('toBeOnPage returns the expectation for chaining', function () {
    $page = new FakePage(pendingBrowser());

    expect(expect($page)->toBeOnPage(ExamplePage::class))->toBeInstanceOf(Expectation::class);
});

it('toSee returns the expectation for chaining', function () {
    $page = new FakePage(pendingBrowser());

    expect(expect($page)->toSee('hello'))->toBeInstanceOf(Expectation::class);
});