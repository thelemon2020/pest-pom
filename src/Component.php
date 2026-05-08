<?php

declare(strict_types=1);

namespace Thelemon2020\PestPom;

use Pest\Browser\Api\AwaitableWebpage;
use Pest\Browser\Api\PendingAwaitablePage;

/**
 * Base class for reusable UI component objects.
 *
 * A Component models a named region of the page (nav bar, data table, modal, etc.)
 * that appears across multiple pages. It holds a reference to the shared browser
 * session and delegates method calls to it, exactly like Page does.
 *
 * When selector() returns a non-empty string, the scoped assertion methods
 * (assertSee, assertVisible, etc.) automatically scope their checks to within
 * that selector. Sub-components composed via component() inherit and extend
 * the parent's resolved selector.
 */
abstract class Component
{
    protected PendingAwaitablePage|AwaitableWebpage $browser;

    private string $resolvedSelector;

    final public function __construct(PendingAwaitablePage|AwaitableWebpage $browser, string $parentSelector = '')
    {
        $this->browser = $browser;

        $own = static::selector();

        if ($own === '') {
            $this->resolvedSelector = '';
        } elseif ($parentSelector === '') {
            $this->resolvedSelector = $own;
        } else {
            $this->resolvedSelector = $parentSelector . ' ' . $own;
        }
    }

    /**
     * The CSS selector that identifies this component's root element on the page.
     * Override in subclasses to enable scoped assertions and sub-component composition.
     */
    public static function selector(): string
    {
        return '';
    }

    /**
     * The fully-resolved CSS selector for this component, including any parent
     * component selector prefix. Empty string when no selector is defined.
     */
    public function resolvedSelector(): string
    {
        return $this->resolvedSelector;
    }

    /**
     * Create a typed sub-Component instance backed by this component's browser session.
     * The sub-component's selector is scoped within this component's resolved selector.
     *
     * @param  class-string<Component>  $componentClass
     */
    public function component(string $componentClass): Component
    {
        return new $componentClass($this->browser, $this->resolvedSelector);
    }

    /**
     * Assert that the given text appears somewhere within this component.
     */
    public function assertSee(string|int|float $text): static
    {
        $this->requireSelector();

        return $this->callBrowser('assertSeeIn', $this->resolvedSelector, $text);
    }

    /**
     * Assert that the given text does not appear within this component.
     */
    public function assertDontSee(string|int|float $text): static
    {
        $this->requireSelector();

        return $this->callBrowser('assertDontSeeIn', $this->resolvedSelector, $text);
    }

    /**
     * Assert that this component's root element is visible on the page.
     */
    public function assertVisible(): static
    {
        $this->requireSelector();

        return $this->callBrowser('assertVisible', $this->resolvedSelector);
    }

    /**
     * Assert that this component's root element is present in the DOM.
     */
    public function assertPresent(): static
    {
        $this->requireSelector();

        return $this->callBrowser('assertPresent', $this->resolvedSelector);
    }

    /**
     * Assert that this component's root element is absent from the DOM.
     */
    public function assertMissing(): static
    {
        $this->requireSelector();

        return $this->callBrowser('assertMissing', $this->resolvedSelector);
    }

    /**
     * Assert that the given number of elements matching $childSelector exist within this component.
     */
    public function assertCount(string $childSelector, int $expected): static
    {
        $this->requireSelector();

        return $this->callBrowser('assertCount', $this->resolvedSelector . ' ' . $childSelector, $expected);
    }

    /**
     * Assert that the given text appears within a child element inside this component.
     */
    public function assertSeeIn(string $childSelector, string|int|float $text): static
    {
        $this->requireSelector();

        return $this->callBrowser('assertSeeIn', $this->resolvedSelector . ' ' . $childSelector, $text);
    }

    /**
     * Assert that the given text does not appear within a child element inside this component.
     */
    public function assertDontSeeIn(string $childSelector, string|int|float $text): static
    {
        $this->requireSelector();

        return $this->callBrowser('assertDontSeeIn', $this->resolvedSelector . ' ' . $childSelector, $text);
    }

    private function requireSelector(): void
    {
        if ($this->resolvedSelector === '') {
            throw new \LogicException(
                'Cannot call a scoped assertion on ' . static::class . ' because selector() returns an empty string.'
            );
        }
    }

    /**
     * Calls a method on the browser and keeps the internal AwaitableWebpage reference
     * up to date, since it is a readonly value object that is re-created on each operation.
     */
    protected function callBrowser(string $method, mixed ...$args): static
    {
        $result = $this->browser->$method(...$args);

        if ($result instanceof AwaitableWebpage) {
            $this->browser = $result;
        }

        return $this;
    }

    /**
     * Delegates all calls to the underlying Pest browser page.
     *
     * @param  array<int, mixed>  $args
     */
    public function __call(string $method, array $args): mixed
    {
        $result = $this->browser->$method(...$args);

        if ($result instanceof AwaitableWebpage) {
            $this->browser = $result;

            return $this;
        }

        return $result;
    }
}
