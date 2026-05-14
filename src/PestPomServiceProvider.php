<?php

declare(strict_types=1);

namespace Thelemon2020\PestPom;

use Illuminate\Support\ServiceProvider;
use Thelemon2020\PestPom\Console\MakeComponentCommand;
use Thelemon2020\PestPom\Console\MakePageCommand;

final class PestPomServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/pest-plugin-pom.php',
            'pest-plugin-pom',
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/pest-plugin-pom.php' => config_path('pest-plugin-pom.php'),
            ], 'pest-plugin-pom-config');

            $this->commands([MakePageCommand::class, MakeComponentCommand::class]);
        }

    }
}