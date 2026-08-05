<?php

declare(strict_types=1);

namespace App\Core;

use Dotenv\Dotenv;

/**
 * Читає конфіг із .env. Змінні оточення мають пріоритет над файлом —
 * це те, що дозволяє docker-compose підмінити DB_HOST, не чіпаючи .env.
 */
final class Config
{
    private static bool $loaded = false;

    public static function load(string $basePath): void
    {
        if (self::$loaded) {
            return;
        }

        if (is_file($basePath . '/.env')) {
            Dotenv::createImmutable($basePath)->safeLoad();
        }

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $raw = $_ENV[$key] ?? getenv($key);

        if ($raw === false || $raw === null || $raw === '') {
            return $default;
        }

        return $raw;
    }

    public static function str(string $key, string $default = ''): string
    {
        return (string) self::get($key, $default);
    }

    public static function int(string $key, int $default = 0): int
    {
        return (int) self::get($key, $default);
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $raw = self::get($key);

        if ($raw === null) {
            return $default;
        }

        return in_array(strtolower((string) $raw), ['1', 'true', 'yes', 'on'], true);
    }

    public static function isProduction(): bool
    {
        return self::str('APP_ENV', 'production') === 'production';
    }
}
