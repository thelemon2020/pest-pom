<?php

declare(strict_types=1);

namespace Thelemon2020\PestPages;

use ReflectionClass;
use RuntimeException;

final class Config
{
    private const DEFAULT_PATH = 'tests/Browser/Pages';

    /**
     * The configured pages path, relative to the project root.
     *
     * Reads from Laravel's config system when available, otherwise falls back
     * to requiring the config file directly so it works in the Pest context too.
     */
    public static function path(): string
    {
        if (function_exists('config')) {
            return config('pest-pages.path', self::DEFAULT_PATH);
        }

        $root = function_exists('base_path') ? base_path() : getcwd();
        $file = $root.'/config/pest-pages.php';

        if (file_exists($file)) {
            return (require $file)['path'] ?? self::DEFAULT_PATH;
        }

        return self::DEFAULT_PATH;
    }

    /**
     * The configured pages path as an absolute filesystem path.
     *
     * If the configured path is already absolute it is returned unchanged,
     * otherwise it is resolved relative to the project root.
     */
    public static function absolutePath(): string
    {
        $path = self::path();

        if (str_starts_with($path, '/') || preg_match('/^[A-Z]:\\\\/i', $path)) {
            return $path;
        }

        $root = function_exists('base_path') ? base_path() : getcwd();

        return $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Throws if $className does not live in the configured pages directory.
     */
    public static function assertPageIsInConfiguredDirectory(string $className): void
    {
        $file = (new ReflectionClass($className))->getFileName();

        if ($file === false) {
            return;
        }

        $sep        = DIRECTORY_SEPARATOR;
        $configured = rtrim(str_replace(['/', '\\'], $sep, self::absolutePath()), $sep);
        $actual     = str_replace(['/', '\\'], $sep, $file);

        if (! str_starts_with($actual, $configured.$sep)) {
            throw new RuntimeException(sprintf(
                'Page [%s] must live in the configured pages directory [%s].',
                $className,
                self::absolutePath(),
            ));
        }
    }
}