<?php

declare(strict_types=1);

namespace Thelemon2020\PestPom\Concerns;

use Thelemon2020\PestPom\Page;

/**
 * @mixin Page|Component
 */
trait InteractsWithForms
{
    /**
     * Fill multiple form fields at once.
     *
     * @param  array<string, string>  $fields  Keyed by field name / label / selector.
     */
    public function fillForm(array $fields): static
    {
        foreach ($fields as $field => $value) {
            $this->type($field, $value);
        }

        return $this;
    }

    /**
     * Click the submit button by its visible label.
     */
    public function submitForm(string $button = 'Submit'): static
    {
        return $this->press($button);
    }

    /**
     * Check a checkbox or radio by its label.
     */
    public function check(string $label): static
    {
        return $this->click($label);
    }

    /**
     * Select an option in a dropdown by its visible label.
     *
     * @param  array<int, string|int>|string|int  $option
     */
    public function choose(string $field, array|string|int $option): static
    {
        return $this->select($field, $option);
    }
}