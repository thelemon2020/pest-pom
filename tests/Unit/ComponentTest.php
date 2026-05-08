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

it('assertSee on a component with a selector scopes to that selector', function () {
    $component = new class(pendingBrowser()) extends ExampleComponent {
        public array $calls = [];
        protected function callBrowser(string $method, mixed ...$args): static
        {
            $this->calls[] = [$method, ...$args];
            return $this;
        }
    };

    $component->assertSee('text');

    expect($component->calls)->toBe([['assertSeeIn', '#example', 'text']]);
});

it('scoped assertions throw LogicException when selector is empty', function () {
    $component = new class(pendingBrowser()) extends Component {};

    $component->assertSee('text');
})->throws(\LogicException::class);

it('component() on a Component scopes the child selector under the parent', function () {
    $childInstance = new class(pendingBrowser()) extends Component {
        public array $calls = [];
        public static function selector(): string { return '#example'; }
        protected function callBrowser(string $method, mixed ...$args): static
        {
            $this->calls[] = [$method, ...$args];
            return $this;
        }
    };

    $parent = new class(pendingBrowser()) extends Component {
        public static function selector(): string { return '#nav'; }
    };

    $child = $parent->component($childInstance::class);
    $child->assertSee('text');

    expect($child->calls)->toBe([['assertSeeIn', '#nav #example', 'text']]);
});

it('a child component with no selector throws even when created under a parent', function () {
    $noSelectorInstance = new class(pendingBrowser()) extends Component {};

    $child = (new ExampleComponent(pendingBrowser()))->component($noSelectorInstance::class);

    $child->assertSee('text');
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