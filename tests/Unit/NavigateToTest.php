<?php

declare(strict_types=1);

use Pest\Browser\Api\PendingAwaitablePage;
use Thelemon2020\PestPom\Tests\Fixtures\AnotherPage;
use Thelemon2020\PestPom\Tests\Fixtures\ExamplePage;

// Returns an ExamplePage whose createVisit() stub avoids starting a real browser.
function navigatablePage(): ExamplePage
{
    return new class(pendingBrowser()) extends ExamplePage {
        protected function createVisit(string $url): PendingAwaitablePage
        {
            return pendingBrowser();
        }
    };
}

it('returns an instance of the target page class', function () {
    expect(navigatablePage()->navigateTo(AnotherPage::class))->toBeInstanceOf(AnotherPage::class);
});

it('returns an instance of the same page class when navigating to itself', function () {
    expect(navigatablePage()->navigateTo(ExamplePage::class))->toBeInstanceOf(ExamplePage::class);
});

it('returns a new instance, not the original page', function () {
    $page = navigatablePage();

    expect($page->navigateTo(ExamplePage::class))->not->toBe($page);
});