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
 */
abstract class Component
{
    protected PendingAwaitablePage|AwaitableWebpage $browser;

    final public function __construct(PendingAwaitablePage|AwaitableWebpage $browser)
    {
        $this->browser = $browser;
    }

    /**
     * The CSS selector that identifies this component's root element on the page.
     */
    abstract public static function selector(): string;

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
