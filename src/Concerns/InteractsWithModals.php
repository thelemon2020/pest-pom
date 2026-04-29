<?php

declare(strict_types=1);

namespace Thelemon2020\PestPom\Concerns;

use Thelemon2020\PestPom\Page;

/**
 * @mixin Page|Component
 */
trait InteractsWithModals
{
    /**
     * Open a modal by clicking its trigger element.
     */
    public function openModal(string $trigger): static
    {
        return $this->click($trigger);
    }

    /**
     * Close a modal by clicking its close button.
     */
    public function closeModal(string $closeButton = 'Close'): static
    {
        return $this->click($closeButton);
    }

    /**
     * Confirm a modal dialog by clicking its confirm button.
     */
    public function confirmModal(string $confirmButton = 'Confirm'): static
    {
        return $this->click($confirmButton);
    }

    /**
     * Dismiss a modal dialog by clicking its cancel button.
     */
    public function dismissModal(string $cancelButton = 'Cancel'): static
    {
        return $this->click($cancelButton);
    }
}