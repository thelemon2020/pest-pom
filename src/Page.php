<?php

declare(strict_types=1);

namespace Thelemon2020\PestPom;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\URL;
use Pest\Browser\Api\AwaitableWebpage;
use Pest\Browser\Api\PendingAwaitablePage;
use Pest\Browser\Support\ComputeUrl;

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

    final public function __construct(PendingAwaitablePage|AwaitableWebpage $browser)
    {
        $this->browser = $browser;
    }

    /**
     * The URL path this page represents.
     */
    abstract public static function url(): string;

    /**
     * Substitute `{param}` placeholders in the URL template.
     *
     * @param  array<string, mixed>  $parameters
     */
    public static function resolveUrl(array $parameters = []): string
    {
        $url = static::url();

        foreach ($parameters as $key => $value) {
            $url = str_replace('{' . $key . '}', (string) $value, $url);
        }

        return $url;
    }

    /**
     * Navigate to this page and return a typed instance.
     *
     * Enforces that the page class lives in the configured pages directory.
     *
     * @param  array<string, mixed>  $parameters  Values for any `{param}` placeholders in `url()`.
     */
    public static function open(array $parameters = []): static
    {
        Config::assertPageIsInConfiguredDirectory(static::class);

        return new static(visit(static::resolveUrl($parameters)));
    }

    /**
     * Navigate to this page pre-authenticated as the given user.
     *
     * Visits a short-lived signed login route (registered only in the testing
     * environment) which logs the user in server-side before redirecting to
     * this page. Works with any session driver.
     *
     * @param  array<string, mixed>  $parameters
     */
    public static function openAsUser(Authenticatable $user, array $parameters = []): static
    {
        Config::assertPageIsInConfiguredDirectory(static::class);

        $targetUrl = static::resolveUrl($parameters);

        $loginUrl = URL::temporarySignedRoute(
            'pest-pom.test-login',
            now()->addMinutes(1),
            [
                'model'    => get_class($user),
                'user'     => $user->getAuthIdentifier(),
                'redirect' => $targetUrl,
            ],
        );

        $pending = static::createAuthVisit($loginUrl, []);

        return new static(static::wrapAsUserVisit($pending, $targetUrl));
    }

    /**
     * Forces the pending browser to resolve (navigating through the login redirect
     * to $targetUrl) then re-wraps it with $targetUrl as the initial URL, so that
     * assertion failure messages reference the page the user intended to visit
     * rather than the internal login endpoint.
     *
     * Override in tests to avoid starting a real browser.
     */
    protected static function wrapAsUserVisit(
        PendingAwaitablePage $pending,
        string $targetUrl,
    ): PendingAwaitablePage|AwaitableWebpage {
        return new AwaitableWebpage($pending->page(), $targetUrl);
    }

    /**
     * Run $setup (e.g. auth()->login(), session()->put()) then navigate to this page
     * with the resulting session injected as a Playwright storageState cookie.
     *
     * Requires a non-array session driver (file or database).
     *
     * @param  array<string, mixed>  $parameters
     */
    public static function openWithState(callable $setup, array $parameters = []): static
    {
        Config::assertPageIsInConfiguredDirectory(static::class);

        if (! session()->isStarted()) {
            session()->start();
        }

        $setup();
        session()->save();

        $domain = parse_url(config('app.url', 'http://localhost'), PHP_URL_HOST) ?? 'localhost';

        $options = [
            'storageState' => [
                'cookies' => [[
                    'name'     => config('session.cookie'),
                    'value'    => session()->getId(),
                    'domain'   => $domain,
                    'path'     => '/',
                    'httpOnly' => true,
                    'secure'   => false,
                    'sameSite' => 'Lax',
                ]],
                'origins' => [],
            ],
        ];

        return new static(static::createAuthVisit(static::resolveUrl($parameters), $options));
    }

    /**
     * Performs a fresh browser visit with context options (e.g. storageState).
     * Extracted so tests can override it without a real Playwright connection.
     *
     * @param  array<string, mixed>  $options
     */
    protected static function createAuthVisit(string $url, array $options): PendingAwaitablePage
    {
        return visit($url, $options);
    }

    /**
     * Create a typed Component instance backed by this page's browser session.
     *
     *
     * @param  class-string<Component>  $componentClass
     * @return Component
     */
    public function component(string $componentClass): Component
    {
        return new $componentClass($this->browser);
    }

    /**
     * Re-wrap the current browser session as a different page type without navigating.
     * Use after an action that causes a server-side redirect (e.g. form submit).
     * Throws if the browser's current URL path does not match the page's URL.
     **
     * @param  class-string<Page>  $pageClass
     * @return Page
     */
    public function nowOn(string $pageClass): Page
    {
        $currentUrl = $this->currentBrowserUrl();

        if ($currentUrl !== null) {
            $currentPath = rtrim((string) parse_url($currentUrl, PHP_URL_PATH), '/');
            $expectedPath = rtrim((string) parse_url($pageClass::url(), PHP_URL_PATH), '/');

            if (! $this->pathMatchesPattern($currentPath, $expectedPath)) {
                throw new \RuntimeException(
                    "Expected to be on [{$expectedPath}] but the browser is at [{$currentPath}]."
                );
            }
        }

        return new $pageClass($this->browser);
    }

    /**
     * Returns the browser's current URL, or null when the browser has not yet been resolved.
     * Extracted so tests can override it without needing a live Playwright connection.
     */
    protected function currentBrowserUrl(): ?string
    {
        if (! $this->browser instanceof AwaitableWebpage) {
            return null;
        }

        return $this->browser->page()->url();
    }

    /**
     * Explicitly navigate to a different page class.
     * Use when an action (e.g. submitting a form) takes you to a new screen.
     **
     * @param  class-string<Page>  $pageClass
     * @param  array<string, mixed>  $parameters  Values for any `{param}` placeholders in the target page's `url()`.
     * @return Page
     */
    public function navigateTo(string $pageClass, array $parameters = []): Page
    {
        $resolvedUrl = $pageClass::resolveUrl($parameters);

        // When the browser is already resolved, reuse the existing Playwright page
        // (and its context) so cookies/session/auth state are preserved.
        // Calling visit() would create a new browser context (fresh incognito), losing the session.
        if ($this->browser instanceof AwaitableWebpage) {
            $url = ComputeUrl::from($resolvedUrl);
            $this->browser->page()->goto($url);

            return new $pageClass(new AwaitableWebpage($this->browser->page(), $url));
        }

        return new $pageClass($this->createVisit($resolvedUrl));
    }

    /**
     * Returns true when $currentPath matches the URL pattern, treating
     * `{param}` segments as wildcards that match any single path segment.
     */
    private function pathMatchesPattern(string $currentPath, string $patternPath): bool
    {
        if (! str_contains($patternPath, '{')) {
            return $currentPath === $patternPath;
        }

        $segments = (array) preg_split('/(\{[^}]+\})/', $patternPath, -1, PREG_SPLIT_DELIM_CAPTURE);
        $regex = '';

        foreach ($segments as $segment) {
            $regex .= preg_match('/^\{[^}]+\}$/', $segment)
                ? '[^/]+'
                : preg_quote($segment, '#');
        }

        return (bool) preg_match('#^' . $regex . '$#', $currentPath);
    }

    /**
     * Performs a fresh browser visit. Extracted so tests can override it
     * without starting a real Playwright connection.
     */
    protected function createVisit(string $url): PendingAwaitablePage
    {
        return visit($url);
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