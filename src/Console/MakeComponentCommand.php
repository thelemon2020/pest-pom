<?php

declare(strict_types=1);

namespace Thelemon2020\PestPom\Console;

use Thelemon2020\PestPom\Config;

final class MakeComponentCommand extends GeneratorCommand
{
    protected $signature = 'pest:component
        {name : The name of the component (e.g. Navbar or NavbarComponent)}
        {--concerns= : Comma-separated concerns to include: forms, alerts, modals, navigation}';

    protected $description = 'Create a new Pest Pages component object';

    protected function resolveClassName(string $name): string
    {
        return str_ends_with($name, 'Component') ? $name : $name.'Component';
    }

    protected function directory(): string
    {
        return Config::componentsAbsolutePath();
    }

    protected function entityLabel(): string
    {
        return 'Component';
    }

    protected function configuredPath(): string
    {
        return dirname(Config::path()).'/Components';
    }

    protected function fallbackNamespace(): string
    {
        return 'Tests\\Browser\\Components';
    }

    protected function buildStub(string $className, string $namespace, string $useStatements, string $traitBlock): string
    {
        return <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use Thelemon2020\PestPom\Component;{$useStatements}

        class {$className} extends Component
        {{$traitBlock}
            public static function selector(): string
            {
                // TODO: return the CSS selector for this component's root element
                return '';
            }
        }

        PHP;
    }
}