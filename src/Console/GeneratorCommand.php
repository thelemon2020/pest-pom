<?php

declare(strict_types=1);

namespace Thelemon2020\PestPom\Console;

use Illuminate\Console\Command;

abstract class GeneratorCommand extends Command
{
    private const CONCERN_MAP = [
        'forms'      => 'InteractsWithForms',
        'alerts'     => 'InteractsWithAlerts',
        'modals'     => 'InteractsWithModals',
        'navigation' => 'InteractsWithNavigation',
    ];

    abstract protected function resolveClassName(string $name): string;

    abstract protected function directory(): string;

    abstract protected function entityLabel(): string;

    abstract protected function configuredPath(): string;

    abstract protected function fallbackNamespace(): string;

    abstract protected function buildStub(string $className, string $namespace, string $useStatements, string $traitBlock): string;

    final public function handle(): int
    {
        $className = $this->resolveClassName($this->argument('name'));
        $concerns  = $this->parseConcerns();
        $directory = $this->directory();
        $namespace = $this->resolveNamespace();
        $filePath  = $directory.DIRECTORY_SEPARATOR.$className.'.php';
        $label     = $this->entityLabel();

        if (file_exists($filePath)) {
            $this->components->error("{$label} [{$filePath}] already exists.");

            return self::FAILURE;
        }

        if (! is_dir($directory) && ! mkdir($directory, 0755, true)) {
            $this->components->error("Failed to create directory [{$directory}].");

            return self::FAILURE;
        }

        if (file_put_contents($filePath, $this->buildClass($className, $namespace, $concerns)) === false) {
            $this->components->error("Failed to write file [{$filePath}].");

            return self::FAILURE;
        }

        $this->components->info("{$label} [{$filePath}] created successfully.");

        return self::SUCCESS;
    }

    final protected function resolveNamespace(): string
    {
        $configuredPath = $this->configuredPath();
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

        return $this->fallbackNamespace();
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

    private function buildClass(string $className, string $namespace, array $concerns): string
    {
        $useStatements = '';
        $traitBlock    = '';

        if ($concerns !== []) {
            $imports = array_map(
                fn (string $c) => "use Thelemon2020\\PestPom\\Concerns\\{$c};",
                $concerns,
            );

            $useStatements = "\n".implode("\n", $imports);
            $traitBlock    = "\n    use ".implode(";\n    use ", $concerns).";\n\n";
        }

        return $this->buildStub($className, $namespace, $useStatements, $traitBlock);
    }
}