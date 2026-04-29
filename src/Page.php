<?php

declare(strict_types=1);

namespace Thelemon2020\PestPages;

use Pest\Browser\Api\AwaitableWebpage;
use Pest\Browser\Api\PendingAwaitablePage;

/**
 * Base class for all page objects.
 *
 * Wraps the Pest browser page returned by visit() and delegates method calls
 * to it, keeping fluent chaining typed to the concrete page subclass.
 */
abstract class Page
{
    /**
     * The underlying browser page. Starts as PendingAwaitablePage after visit(),
     * then transitions to AwaitableWebpage once the first method is called.
     */
    protected PendingAwaitablePage|AwaitableWebpage $browser;

    final public function __construct(PendingAwaitablePage $browser)
    {
        $this->browser = $browser;
    }

    /**
     * The URL path this page represents.
     */
    abstract public static function url(): string;

    /**
     * Navigate to this page and return a typed instance.
     *
     * Enforces that the page class lives in the configured pages directory.
     */
    public static function open(): static
    {
        Config::assertPageIsInConfiguredDirectory(static::class);

        return new static(visit(static::url()));
    }

    /**
     * Explicitly navigate to a different page class.
     * Use when an action (e.g. submitting a form) takes you to a new screen.
     *
     * @template TPage of Page
     *
     * @param  class-string<TPage>  $pageClass
     * @return TPage
     */
    public function navigateTo(string $pageClass): Page
    {
        return new $pageClass(visit($pageClass::url()));
    }

    /**
     * Delegates all calls to the underlying Pest browser page.
     *
     * When the browser returns itself (chainable methods like click, type,
     * assertSee, etc.), we update our internal reference and return $this so
     * the chain stays typed to the concrete Page subclass.
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