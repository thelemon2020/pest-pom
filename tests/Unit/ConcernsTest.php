<?php

declare(strict_types=1);

use Thelemon2020\PestPom\Concerns\InteractsWithAlerts;
use Thelemon2020\PestPom\Concerns\InteractsWithForms;
use Thelemon2020\PestPom\Concerns\InteractsWithModals;
use Thelemon2020\PestPom\Concerns\InteractsWithNavigation;
use Thelemon2020\PestPom\Tests\Fixtures\FakePage;

function pageWithAlerts(): FakePage
{
    return new class(pendingBrowser()) extends FakePage {
        use InteractsWithAlerts;
    };
}

function pageWithForms(): FakePage
{
    return new class(pendingBrowser()) extends FakePage {
        use InteractsWithForms;
    };
}

function pageWithModals(): FakePage
{
    return new class(pendingBrowser()) extends FakePage {
        use InteractsWithModals;
    };
}

function pageWithNavigation(): FakePage
{
    return new class(pendingBrowser()) extends FakePage {
        use InteractsWithNavigation;
    };
}

describe('InteractsWithAlerts', function () {
    it('assertSuccessMessage delegates to assertSee', function () {
        $page = pageWithAlerts();
        $page->assertSuccessMessage('Changes saved.');
        expect($page->calls)->toContain(['method' => 'assertSee', 'args' => ['Changes saved.']]);
    });

    it('assertErrorMessage delegates to assertSee', function () {
        $page = pageWithAlerts();
        $page->assertErrorMessage('Something went wrong.');
        expect($page->calls)->toContain(['method' => 'assertSee', 'args' => ['Something went wrong.']]);
    });

    it('assertFieldError delegates to assertSeeIn with the field selector and message', function () {
        $page = pageWithAlerts();
        $page->assertFieldError('email', 'The email field is required.');
        expect($page->calls)->toContain(['method' => 'assertSeeIn', 'args' => ['email', 'The email field is required.']]);
    });

    it('assertSuccessMessage returns $this for chaining', function () {
        $page = pageWithAlerts();
        expect($page->assertSuccessMessage('ok'))->toBe($page);
    });
});

describe('InteractsWithForms', function () {
    it('fillForm calls type for each field', function () {
        $page = pageWithForms();
        $page->fillForm(['email' => 'a@b.com', 'name' => 'Alice']);
        expect($page->calls)->toContain(['method' => 'type', 'args' => ['email', 'a@b.com']]);
        expect($page->calls)->toContain(['method' => 'type', 'args' => ['name', 'Alice']]);
    });

    it('fillForm returns $this for chaining', function () {
        $page = pageWithForms();
        expect($page->fillForm(['x' => 'y']))->toBe($page);
    });

    it('submitForm delegates to press with the given label', function () {
        $page = pageWithForms();
        $page->submitForm('Login');
        expect($page->calls)->toContain(['method' => 'press', 'args' => ['Login']]);
    });

    it('submitForm defaults to Submit', function () {
        $page = pageWithForms();
        $page->submitForm();
        expect($page->calls)->toContain(['method' => 'press', 'args' => ['Submit']]);
    });

    it('check delegates to click with the label', function () {
        $page = pageWithForms();
        $page->check('Remember me');
        expect($page->calls)->toContain(['method' => 'click', 'args' => ['Remember me']]);
    });

    it('choose delegates to select with field and option', function () {
        $page = pageWithForms();
        $page->choose('country', 'Canada');
        expect($page->calls)->toContain(['method' => 'select', 'args' => ['country', 'Canada']]);
    });
});

describe('InteractsWithModals', function () {
    it('openModal delegates to click with the trigger', function () {
        $page = pageWithModals();
        $page->openModal('Delete Account');
        expect($page->calls)->toContain(['method' => 'click', 'args' => ['Delete Account']]);
    });

    it('closeModal delegates to click with the given label', function () {
        $page = pageWithModals();
        $page->closeModal('Dismiss');
        expect($page->calls)->toContain(['method' => 'click', 'args' => ['Dismiss']]);
    });

    it('closeModal defaults to Close', function () {
        $page = pageWithModals();
        $page->closeModal();
        expect($page->calls)->toContain(['method' => 'click', 'args' => ['Close']]);
    });

    it('confirmModal delegates to click with the given label', function () {
        $page = pageWithModals();
        $page->confirmModal('Yes, delete');
        expect($page->calls)->toContain(['method' => 'click', 'args' => ['Yes, delete']]);
    });

    it('confirmModal defaults to Confirm', function () {
        $page = pageWithModals();
        $page->confirmModal();
        expect($page->calls)->toContain(['method' => 'click', 'args' => ['Confirm']]);
    });

    it('dismissModal delegates to click with the given label', function () {
        $page = pageWithModals();
        $page->dismissModal('No thanks');
        expect($page->calls)->toContain(['method' => 'click', 'args' => ['No thanks']]);
    });

    it('dismissModal defaults to Cancel', function () {
        $page = pageWithModals();
        $page->dismissModal();
        expect($page->calls)->toContain(['method' => 'click', 'args' => ['Cancel']]);
    });
});

describe('InteractsWithNavigation', function () {
    it('clickLink delegates to click with the label', function () {
        $page = pageWithNavigation();
        $page->clickLink('Home');
        expect($page->calls)->toContain(['method' => 'click', 'args' => ['Home']]);
    });

    it('goBack delegates to back', function () {
        $page = pageWithNavigation();
        $page->goBack();
        expect($page->calls)->toContain(['method' => 'back', 'args' => []]);
    });

    it('goForward delegates to forward', function () {
        $page = pageWithNavigation();
        $page->goForward();
        expect($page->calls)->toContain(['method' => 'forward', 'args' => []]);
    });

    it('refresh delegates to reload', function () {
        $page = pageWithNavigation();
        $page->refresh();
        expect($page->calls)->toContain(['method' => 'reload', 'args' => []]);
    });

    it('clickLink returns $this for chaining', function () {
        $page = pageWithNavigation();
        expect($page->clickLink('About'))->toBe($page);
    });
});