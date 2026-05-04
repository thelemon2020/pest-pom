<?php

declare(strict_types=1);

use Thelemon2020\PestPom\Component;
use Thelemon2020\PestPom\Tests\Fixtures\ExampleComponent;
use Thelemon2020\PestPom\Tests\Fixtures\ExamplePage;

it('returns an instance of the given component class', function () {
    $page = new ExamplePage(pendingBrowser());

    expect($page->component(ExampleComponent::class))->toBeInstanceOf(ExampleComponent::class);
});

it('returns a distinct instance on each call', function () {
    $page = new ExamplePage(pendingBrowser());

    expect($page->component(ExampleComponent::class))
        ->not->toBe($page->component(ExampleComponent::class));
});

it('ExampleComponent selector returns its configured value', function () {
    expect(ExampleComponent::selector())->toBe('#example');
});

it('a component subclass with no selector override returns an empty string', function () {
    $component = new class(pendingBrowser()) extends Component {};

    expect($component::selector())->toBe('');
});