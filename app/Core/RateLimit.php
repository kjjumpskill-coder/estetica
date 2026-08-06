<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Обмеження частоти заявок.
 *
 * Рахуємо по хешу IP, а не по самій IP: для обмеження достатньо знати,
 * що це та сама адреса, а зберігати її — зайві персональні дані.
 * Сіль спільна з аналітикою і змінюється щодоби, тож зв'язок «хеш → людина»
 * не переживає добу.
 */
final class RateLimit
{
    private const MAX_PER_HOUR = 3;

    public static function ipHash(): string
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        $ip = trim(explode(',', (string) $ip)[0]);

        return hash('sha256', $ip . '|' . Config::str('ANALYTICS_SALT') . '|' . date('Y-m-d'));
    }

    /** Чи вичерпано ліміт для поточного відвідувача. */
    public static function exceeded(string $ipHash): bool
    {
        $count = (int) Db::scalar(
            'SELECT COUNT(*) FROM leads WHERE ip_hash = ? AND created_at > (NOW() - INTERVAL 1 HOUR)',
            [$ipHash]
        );

        return $count >= self::MAX_PER_HOUR;
    }
}
