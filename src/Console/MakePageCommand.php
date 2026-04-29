<?php

declare(strict_types=1);

namespace Thelemon2020\PestPages\Console;

use Illuminate\Console\Command;
use Thelemon2020\PestPages\Config;

final class MakePageCommand extends Command
{
    protected $signature = 'pest:page
        {name : The name of the page (e.g. Login or LoginPage)}
        {--concerns= : Comma-separated concerns to include: forms, alerts, modals, navigation}';

    protected $description = 'Create a new Pest Pages page object';

    private const CONCERN_MAP = [
        'forms'      => 'InteractsWithForms',
        'alerts'     => 'InteractsWithAlerts',
        'modals'     => 'InteractsWithModals',
        'navigation' => 'InteractsWithNavigation',
    ];

    public function handle(): int
    {
        $className = $this->resolveClassName($this->argument('name'));
        $concerns  = $this->parseConcerns();
        $directory = Config::absolutePath();
        $namespace = $this->resolveNamespace();
        $filePath  = $directory.DIRECTORY_SEPARATOR.$className.'.php';

        if (file_exists($filePath)) {
            $this->components->error("Page [{$filePath}] already exists.");

            return self::FAILURE;
        }

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($filePath, $this->buildClass($className, $namespace, $concerns));

        $this->components->info("Page [{$filePath}] created successfully.");

        return self::SUCCESS;
    }

    private function resolveClassName(string $name): string
    {
        return str_ends_with($name, 'Page') ? $name : $name.'Page';
    }

    private function parseConcerns(): array
    {
        $input = $this->option('concerns');

        if (! $input) {
            return [];
        }

        $concerns = [];

        foreach (explode(',', $input) as $raw) {
            $key = strtolower(trim($raw));

            if (isset(self::CONCERN_MAP[$key])) {
                $concerns[] = self::CONCERN_MAP[$key];
            } else {
                $this->components->warn("Unknown concern [{$raw}]. Valid options: ".implode(', ', array_keys(self::CONCERN_MAP)));
            }
        }

        return $concerns;
    }

    private function resolveNamespace(): string
    {
        $configuredPath = Config::path();
        $composerPath   = base_path('composer.json');

        if (file_exists($composerPath)) {
            $composer = json_decode(file_get_contents($composerPath), true);

            foreach ($composer['autoload-dev']['psr-4'] ?? [] as $namespace => $path) {
                $normalizedRoot = rtrim($path, '/');

                if ($configuredPath === $normalizedRoot) {
                    return rtrim($namespace, '\\');
                }

                if (str_starts_with($configuredPath, $normalizedRoot.'/')) {
                    $remainder = substr($configuredPath, strlen($normalizedRoot) + 1);

                    return rtrim($namespace, '\\').'\\'.str_replace('/', '\\', $remainder);
                }
            }
        }

        return 'Tests\\Browser\\Pages';
    }

    private function buildClass(string $className, string $namespace, array $concerns): string
    {
        $useStatements = '';
        $traitBlock    = '';

        if ($concerns !== []) {
            $imports = array_map(
                fn (string $c) => "use Thelemon2020\\PestPages\\Concerns\\{$c};",
                $concerns,
            );

            $useStatements = "\n".implode("\n", $imports);
            $traitBlock    = "\n    use ".implode(";\n    use ", $concerns).";\n\n";
        }

        return <<<PHP
        <?php

        declare(strict_types=1);

        namespace {$namespace};

        use Thelemon2020\PestPages\Page;{$useStatements}

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