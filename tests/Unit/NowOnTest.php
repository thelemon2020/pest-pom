<?php

declare(strict_types=1);

use Thelemon2020\PestPom\Tests\Fixtures\AnotherPage;
use Thelemon2020\PestPom\Tests\Fixtures\ExamplePage;

// Creates an ExamplePage that reports being at $url, without a live browser.
function examplePageAt(string $url): ExamplePage
{
    $page = new class(pendingBrowser()) extends ExamplePage {
        public string $fakeUrl = '';

        protected function currentBrowserUrl(): ?string
        {
            return $this->fakeUrl;
        }
    };

    $page->fakeUrl = $url;

    return $page;
}

it('returns a page of the target type when URLs match', function () {
    $page = examplePageAt('http://localhost/example');

    expect($page->nowOn(ExamplePage::class))->toBeInstanceOf(ExamplePage::class);
});

it('skips the URL check when the browser has not yet been resolved', function () {
    $page = new ExamplePage(pendingBrowser());

    expect($page->nowOn(ExamplePage::class))->toBeInstanceOf(ExamplePage::class);
});

it('throws when the browser is on the wrong page', function () {
    $page = examplePageAt('http://localhost/example');

    $page->nowOn(AnotherPage::class);
})->throws(RuntimeException::class);

it('includes the expected path in the exception', function () {
    $page = examplePageAt('http://localhost/example');

    $page->nowOn(AnotherPage::class);
})->throws(RuntimeException::class, '/another');

it('includes the actual path in the exception', function () {
    $page = examplePageAt('http://localhost/example');

    $page->nowOn(AnotherPage::class);
})->throws(RuntimeException::class, '/example');

it('ignores trailing slashes when comparing paths', function () {
    $page = examplePageAt('http://localhost/example/');

    expect($page->nowOn(ExamplePage::class))->toBeInstanceOf(ExamplePage::class);
});

it('ignores query strings when comparing paths', function () {
    $page = examplePageAt('http://localhost/example?welcome=1');

    expect($page->nowOn(ExamplePage::class))->toBeInstanceOf(ExamplePage::class);
});