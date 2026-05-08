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

it('resolvedSelector returns the own selector when there is no parent', function () {
    expect((new ExampleComponent(pendingBrowser()))->resolvedSelector())->toBe('#example');
});

it('resolvedSelector returns empty string when there is no selector', function () {
    $component = new class(pendingBrowser()) extends Component {};

    expect($component->resolvedSelector())->toBe('');
});

it('component() on a Component scopes the child selector under the parent', function () {
    $parent = new class(pendingBrowser()) extends Component {
        public static function selector(): string { return '#nav'; }
    };

    $child = $parent->component(ExampleComponent::class);

    expect($child->resolvedSelector())->toBe('#nav #example');
});

it('a child component with no selector has an empty resolvedSelector even under a parent', function () {
    $noSelectorComponent = new class(pendingBrowser()) extends Component {};

    $child = (new ExampleComponent(pendingBrowser()))->component($noSelectorComponent::class);

    expect($child->resolvedSelector())->toBe('');
});

it('scoped assertions throw LogicException when selector is empty', function () {
    $component = new class(pendingBrowser()) extends Component {};

    $component->assertSee('text');
})->throws(\LogicException::class);

it('assertSee delegates to assertSeeIn with the resolved selector', function () {
    $component = new class(pendingBrowser()) extends Component {
        public array $calls = [];
        public static function selector(): string { return '#nav'; }
        protected function callBrowser(string $method, mixed ...$args): static
        {
            $this->calls[] = [$method, ...$args];
            return $this;
        }
    };

    $component->assertSee('Hello');

    expect($component->calls)->toBe([['assertSeeIn', '#nav', 'Hello']]);
});

it('assertCount composes the child selector under the resolved selector', function () {
    $component = new class(pendingBrowser()) extends Component {
        public array $calls = [];
        public static function selector(): string { return '#nav'; }
        protected function callBrowser(string $method, mixed ...$args): static
        {
            $this->calls[] = [$method, ...$args];
            return $this;
        }
    };

    $component->assertCount('.item', 3);

    expect($component->calls)->toBe([['assertCount', '#nav .item', 3]]);
});