<?php

declare(strict_types=1);

namespace Thelemon2020\PestPages;

use Illuminate\Support\ServiceProvider;
use Thelemon2020\PestPages\Console\MakePageCommand;

final class PestPagesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/pest-pages.php',
            'pest-pages',
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/pest-pages.php' => config_path('pest-pages.php'),
            ], 'pest-pages-config');

            $this->commands([MakePageCommand::class]);
        }
    }
}