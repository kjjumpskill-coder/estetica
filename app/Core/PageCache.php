<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Статичний кеш головної сторінки.
 *
 * Сенс у тому, що при рекламному трафіку веб-сервер віддає готовий HTML напряму
 * з public_html/cache/index.html і PHP взагалі не стартує. Ліміт 100 CPU-хвилин на добу
 * на тарифі «Швидкий» інакше вигорає за кілька годин.
 *
 * Правило запису: кеш пишеться лише для анонімного GET без query-рядка.
 * Сторінка з ?utm_source=... не має потрапити в кеш, бо тоді її мітки
 * дістануться всім наступним відвідувачам.
 */
final class PageCache
{
    private const FILE = '/public_html/cache/index.html';

    public static function isEnabled(): bool
    {
        // Поза production кеш вимкнений завжди, і це не зручність, а безпека:
        // готовий cache/index.html Apache віддає напряму, ще до старту PHP.
        // Тобто закешована сторінка тестового майданчика поверталася б в обхід
        // пароля, яким ми його закрили.
        if (!Config::isProduction()) {
            return false;
        }

        return Config::int('CACHE_TTL', 0) > 0;
    }

    /** Чи можна віддати збережену копію цьому запиту. */
    public static function isFresh(): bool
    {
        if (!self::isEnabled()) {
            return false;
        }

        $file = BASE_PATH . self::FILE;

        if (!is_file($file)) {
            return false;
        }

        return (time() - (int) filemtime($file)) < Config::int('CACHE_TTL');
    }

    public static function read(): ?string
    {
        $html = @file_get_contents(BASE_PATH . self::FILE);

        return $html === false ? null : $html;
    }

    public static function write(string $html): void
    {
        if (!self::isEnabled() || !self::isCacheableRequest()) {
            return;
        }

        $file = BASE_PATH . self::FILE;
        $dir  = dirname($file);

        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            return;
        }

        // Пишемо через тимчасовий файл: інакше паралельний запит може прочитати
        // недописаний HTML.
        $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';

        if (@file_put_contents($tmp, $html, LOCK_EX) !== false) {
            @rename($tmp, $file);
        } else {
            @unlink($tmp);
        }
    }

    /** Викликається з адмінки після будь-якого збереження. */
    public static function invalidate(): void
    {
        @unlink(BASE_PATH . self::FILE);
    }

    private static function isCacheableRequest(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET'
            && ($_SERVER['QUERY_STRING'] ?? '') === ''
            && session_status() !== PHP_SESSION_ACTIVE;
    }
}
