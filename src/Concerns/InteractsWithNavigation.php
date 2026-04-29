<?php

declare(strict_types=1);

namespace Thelemon2020\PestPom\Concerns;

use Thelemon2020\PestPom\Page;

/**
 * @mixin Page|Component
 */
trait InteractsWithNavigation
{
    /**
     * Click a navigation link by its visible label.
     */
    public function clickLink(string $label): static
    {
        return $this->click($label);
    }

    /**
     * Navigate back in browser history.
     */
    public function goBack(): static
    {
        return $this->back();
    }

    /**
     * Navigate forward in browser history.
     */
    public function goForward(): static
    {
        return $this->forward();
    }

    /**
     * Reload the current page.
     */
    public function refresh(): static
    {
        return $this->reload();
    }
}