<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Authenticatable;
use Pest\Browser\Api\PendingAwaitablePage;
use Thelemon2020\PestPom\Tests\Fixtures\ExamplePage;
use Thelemon2020\PestPom\Tests\Fixtures\ParameterizedPage;

function fakeUser(): Authenticatable
{
    return new class implements Authenticatable
    {
        public function getAuthIdentifierName(): string { return 'id'; }

        public function getAuthIdentifier(): mixed { return 1; }

        public function getAuthPasswordName(): string { return 'password'; }

        public function getAuthPassword(): string { return ''; }

        public function getRememberToken(): ?string { return null; }

        public function setRememberToken($value): void {}

        public function getRememberTokenName(): string { return 'remember_token'; }
    };
}

// An anonymous ExamplePage subclass whose createAuthVisit() avoids a real browser.
function authablePage(): string
{
    return (new class(pendingBrowser()) extends ExamplePage {
        protected static function createAuthVisit(string $url, array $options): PendingAwaitablePage
        {
            return pendingBrowser();
        }
    })::class;
}

function authableParameterizedPage(): string
{
    return (new class(pendingBrowser()) extends ParameterizedPage {
        protected static function createAuthVisit(string $url, array $options): PendingAwaitablePage
        {
            return pendingBrowser();
        }
    })::class;
}

beforeEach(function () {
    config(['pest-plugin-pom.path' => __DIR__]);
});

it('openAsUser() returns an instance of the correct page class', function () {
    $class = authablePage();

    expect($class::openAsUser(fakeUser()))->toBeInstanceOf(ExamplePage::class);
});

it('openAsUser() with parameters returns an instance of the correct page class', function () {
    $class = authableParameterizedPage();

    expect($class::openAsUser(fakeUser(), ['id' => 5]))->toBeInstanceOf(ParameterizedPage::class);
});

it('openWithState() returns an instance of the correct page class', function () {
    $class = authablePage();

    expect($class::openWithState(fn () => null))->toBeInstanceOf(ExamplePage::class);
});

it('openWithState() calls the setup callable before visiting', function () {
    $class = authablePage();
    $called = false;

    $class::openWithState(function () use (&$called) {
        $called = true;
    });

    expect($called)->toBeTrue();
});
