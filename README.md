# Pest Pages

A [Pest](https://pestphp.com) plugin for writing expressive browser tests using the **Page Object Model** pattern.

Page Objects keep your browser tests readable and maintainable by encapsulating page-specific selectors and interactions into dedicated classes. The plugin integrates with [pest-plugin-browser](https://github.com/pestphp/pest-plugin-browser) and automatically starts the Playwright server for any test that uses a Page Object — no manual setup required.

---

## Requirements

- PHP ^8.3
- Pest ^4.0
- pest-plugin-browser ^4.0

## Installation

```bash
composer require thelemon2020/pest-pages --dev
```

### Publishing the config

```bash
php artisan vendor:publish --tag=pest-pages-config
```

This creates `config/pest-pages.php`:

```php
return [
    'path' => 'tests/Browser/Pages',
];
```

`path` controls where the `pest:page` generator writes new files and where `Page::open()` expects page classes to live at runtime. Change it if your project uses a different directory.

---

## Core Concepts

### Page Objects

Each page in your application is represented by a class that extends `Page`. The class defines the URL for the page and exposes methods that describe meaningful user interactions — clicking a button, filling a form, asserting content — in the language of your application rather than raw browser commands.

```php
namespace Tests\Browser\Pages;

use Thelemon2020\PestPages\Page;
use Thelemon2020\PestPages\Concerns\InteractsWithForms;

class LoginPage extends Page
{
    use InteractsWithForms;

    public static function url(): string
    {
        return '/login';
    }

    public function loginAs(string $email, string $password): static
    {
        return $this
            ->fillForm([
                'Email' => $email,
                'Password' => $password,
            ])
            ->submitForm('Log in');
    }
}
```

### Navigating to a Page

Use the `page()` helper or the static `::open()` method to navigate to a page and receive a typed instance:

```php
$page = page(LoginPage::class);

// or equivalently
$page = LoginPage::open();
```

Both return an instance of `LoginPage`, giving you full IDE autocompletion for any methods you have defined on that class.

### Fluent Chaining

Every method on a Page Object returns `static`, so you can chain interactions naturally:

```php
page(LoginPage::class)
    ->loginAs('jane@example.com', 'password')
    ->assertSee('Welcome back, Jane');
```

---

## Writing Tests

The plugin automatically detects any test that calls `page()` or `::open()` and marks it as a browser test, starting the Playwright server as needed. You do not need to annotate tests or configure anything.

```php
it('allows a user to log in', function () {
    page(LoginPage::class)
        ->loginAs('jane@example.com', 'password')
        ->assertSee('Dashboard');
});
```

---

## Creating Page Objects

### Artisan Generator

The quickest way to create a page is with the included Artisan command:

```bash
php artisan pest:page Login
```

This creates `tests/Browser/Pages/LoginPage.php` with a basic scaffold. The `Page` suffix is optional — it won't be doubled if you include it.

Pass `--concerns` to include traits in the generated class:

```bash
php artisan pest:page Register --concerns=forms,alerts
php artisan pest:page UserSettings --concerns=forms,alerts,modals,navigation
```

Available concern names: `forms`, `alerts`, `modals`, `navigation`.

The generated file for `pest:page Register --concerns=forms,alerts` looks like:

```php
<?php

declare(strict_types=1);

namespace Tests\Browser\Pages;

use Thelemon2020\PestPages\Page;
use Thelemon2020\PestPages\Concerns\InteractsWithForms;
use Thelemon2020\PestPages\Concerns\InteractsWithAlerts;

class RegisterPage extends Page
{
    use InteractsWithForms;
    use InteractsWithAlerts;

    public static function url(): string
    {
        return '/';
    }
}
```

The namespace is inferred automatically from your project's `composer.json` `autoload-dev` PSR-4 map, falling back to `Tests\Browser\Pages`.

### Manually

Create a class for each page (or distinct section of a page) in your application, extending the abstract `Page` base class.

```php
namespace Tests\Browser\Pages;

use Thelemon2020\PestPages\Page;

class DashboardPage extends Page
{
    public static function url(): string
    {
        return '/dashboard';
    }

    public function assertWelcomeMessage(string $name): static
    {
        return $this->assertSee("Welcome, {$name}");
    }
}
```

### Navigating Between Pages

When an action on one page causes a navigation to another, use `navigateTo()` to get a typed instance of the destination page:

```php
it('redirects to the dashboard after login', function () {
    $dashboard = page(LoginPage::class)
        ->loginAs('jane@example.com', 'password')
        ->navigateTo(DashboardPage::class);

    $dashboard->assertWelcomeMessage('Jane');
});
```

---

## Available Concerns (Traits)

The plugin ships with four traits that cover common browser interactions. Include only the ones each Page Object needs.

### `InteractsWithForms`

```php
use Thelemon2020\PestPages\Concerns\InteractsWithForms;

class RegistrationPage extends Page
{
    use InteractsWithForms;

    public static function url(): string
    {
        return '/register';
    }
}
```

| Method | Description |
|--------|-------------|
| `fillForm(array $fields)` | Fill multiple fields at once, keyed by label |
| `submitForm(string $button = 'Submit')` | Click a submit button by its label |
| `check(string $label)` | Check a checkbox or radio button by label |
| `choose(string $field, array\|string\|int $option)` | Select a dropdown option by label |

```php
page(RegistrationPage::class)
    ->fillForm([
        'Name'     => 'Jane Doe',
        'Email'    => 'jane@example.com',
        'Password' => 'super-secret',
    ])
    ->check('I agree to the terms and conditions')
    ->choose('Country', 'United States')
    ->submitForm('Create account');
```

---

### `InteractsWithAlerts`

```php
use Thelemon2020\PestPages\Concerns\InteractsWithAlerts;

class ProfilePage extends Page
{
    use InteractsWithAlerts;

    public static function url(): string
    {
        return '/profile';
    }
}
```

| Method | Description |
|--------|-------------|
| `assertSuccessMessage(string $message)` | Assert a success alert or flash message is visible |
| `assertErrorMessage(string $message)` | Assert an error alert or flash message is visible |
| `assertFieldError(string $field, string $message)` | Assert a validation error is visible for a specific field |

```php
page(ProfilePage::class)
    ->fillForm(['Name' => ''])
    ->submitForm('Save')
    ->assertFieldError('Name', 'The name field is required.')
    ->assertErrorMessage('Your profile could not be saved.');
```

---

### `InteractsWithModals`

```php
use Thelemon2020\PestPages\Concerns\InteractsWithModals;

class UserListPage extends Page
{
    use InteractsWithModals;

    public static function url(): string
    {
        return '/users';
    }
}
```

| Method | Description |
|--------|-------------|
| `openModal(string $trigger)` | Click the element that opens the modal |
| `confirmModal(string $confirmButton = 'Confirm')` | Click the confirm button inside the modal |
| `dismissModal(string $cancelButton = 'Cancel')` | Click the cancel button inside the modal |
| `closeModal(string $closeButton = 'Close')` | Close the modal without confirming |

```php
page(UserListPage::class)
    ->openModal('Delete Jane Doe')
    ->confirmModal('Yes, delete user')
    ->assertSuccessMessage('User deleted.');
```

---

### `InteractsWithNavigation`

```php
use Thelemon2020\PestPages\Concerns\InteractsWithNavigation;

class ArticlePage extends Page
{
    use InteractsWithNavigation;

    public static function url(): string
    {
        return '/articles';
    }
}
```

| Method | Description |
|--------|-------------|
| `clickLink(string $label)` | Click a navigation link by its visible text |
| `goBack()` | Navigate back in browser history |
| `goForward()` | Navigate forward in browser history |
| `refresh()` | Reload the current page |

```php
page(ArticlePage::class)
    ->clickLink('Getting Started')
    ->assertSee('Introduction')
    ->goBack()
    ->assertSee('All Articles');
```

---

## Custom Expectations

The plugin registers two Pest expectations for Page Objects:

```php
expect($page)->toBeOnPage(DashboardPage::class);
expect($page)->toSee('Welcome back');
```

| Expectation | Description |
|-------------|-------------|
| `toBeOnPage(string $pageClass)` | Asserts the current URL contains the page's defined URL path |
| `toSee(string $text)` | Asserts the given text is visible on the page |

---

## Full Example

Here is a complete end-to-end registration flow demonstrating Page Objects, traits, navigation, and expectations working together.

**Page Objects:**

```php
// tests/Browser/Pages/RegistrationPage.php
namespace Tests\Browser\Pages;

use Thelemon2020\PestPages\Page;
use Thelemon2020\PestPages\Concerns\InteractsWithAlerts;
use Thelemon2020\PestPages\Concerns\InteractsWithForms;

class RegistrationPage extends Page
{
    use InteractsWithForms;
    use InteractsWithAlerts;

    public static function url(): string
    {
        return '/register';
    }

    public function register(string $name, string $email, string $password): static
    {
        return $this
            ->fillForm([
                'Name'     => $name,
                'Email'    => $email,
                'Password' => $password,
            ])
            ->check('I agree to the terms')
            ->submitForm('Create account');
    }
}
```

```php
// tests/Browser/Pages/DashboardPage.php
namespace Tests\Browser\Pages;

use Thelemon2020\PestPages\Page;
use Thelemon2020\PestPages\Concerns\InteractsWithNavigation;

class DashboardPage extends Page
{
    use InteractsWithNavigation;

    public static function url(): string
    {
        return '/dashboard';
    }

    public function assertUserIsLoggedIn(string $name): static
    {
        return $this->assertSee("Welcome, {$name}");
    }
}
```

**Tests:**

```php
// tests/Browser/RegistrationTest.php
use Tests\Browser\Pages\DashboardPage;
use Tests\Browser\Pages\RegistrationPage;

it('allows a new user to register', function () {
    $dashboard = page(RegistrationPage::class)
        ->register('Jane Doe', 'jane@example.com', 'password')
        ->navigateTo(DashboardPage::class);

    expect($dashboard)
        ->toBeOnPage(DashboardPage::class)
        ->toSee('Welcome, Jane Doe');
});

it('shows a validation error when the email is taken', function () {
    page(RegistrationPage::class)
        ->register('Jane Doe', 'existing@example.com', 'password')
        ->assertFieldError('Email', 'This email is already in use.')
        ->assertErrorMessage('Registration failed. Please correct the errors below.');
});
```

---

## License

MIT