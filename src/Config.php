<?php

declare(strict_types=1);

namespace Thelemon2020\PestPages;

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
     */
    public static function absolutePath(): string
    {
        $relative = self::path();
        $root     = function_exists('base_path') ? base_path() : getcwd();

        return $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
    }
}