<?php

declare(strict_types=1);

use Pest\Browser\Api\PendingAwaitablePage;
use Pest\Browser\Enums\BrowserType;
use Pest\Browser\Enums\Device;

pest()->extend(Thelemon2020\PestPom\Tests\TestCase::class)->in('.');

function pendingBrowser(): PendingAwaitablePage
{
    return new PendingAwaitablePage(BrowserType::CHROME, Device::DESKTOP, '/example', []);
}