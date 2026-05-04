<?php

declare(strict_types=1);

namespace Thelemon2020\PestPom\Console;

use Thelemon2020\PestPom\Config;

final class MakePageCommand extends GeneratorCommand
{
    protected $signature = 'pest:page
        {name : The name of the page (e.g. Login or LoginPage)}
        {--concerns= : Comma-separated concerns to include: forms, alerts, modals, navigation}';

    protected $description = 'Create a new Pest Pages page object';

    protected function resolveClassName(string $name): string
    {
        return str_ends_with($name, 'Page') ? $name : $name.'Page';
    }

    protected function directory(): string
    {
        return Config::absolutePath();
    }

    protected function entityLabel(): string
    {
        return 'Page';
    }

    protected function configuredPath(): string
    {
        return Config::path();
    }

    protected function fallbackNamespace(): string
    {
        return 'Tests\\Browser\\Pages';
    }

    protected function buildStub(string $className, string $namespace, string $useStatements, string $traitBlock): string
    {
        return <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use Thelemon2020\PestPom\Page;{$useStatements}

        class {$className} extends Page
        {{$traitBlock}
            public static function url(): string
            {
                return '/';
            }
        }

        PHP;
    }
}