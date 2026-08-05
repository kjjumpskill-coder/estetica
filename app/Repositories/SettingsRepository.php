<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

/**
 * Налаштування сайту. Читаються одним запитом і тримаються в пам'яті на час запиту —
 * шаблони звертаються до них десятки разів.
 *
 * Порожнє значення = «даних ще немає». Шаблон у такому разі ховає елемент, а не малює
 * заглушку. Тому has() важливіший за get().
 */
final class SettingsRepository
{
    /** @var array<string,string>|null */
    private static ?array $cache = null;

    /** @return array<string,string> */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $rows = Db::select('SELECT `key`, `value` FROM settings');

        self::$cache = array_column($rows, 'value', 'key');

        return self::$cache;
    }

    public static function get(string $key, string $default = ''): string
    {
        $value = self::all()[$key] ?? '';

        return $value === '' ? $default : $value;
    }

    /** Чи заповнене значення. Основний спосіб вирішити, показувати блок чи ні. */
    public static function has(string $key): bool
    {
        return trim(self::all()[$key] ?? '') !== '';
    }

    public static function int(string $key, int $default = 0): int
    {
        $value = self::get($key);

        return $value === '' ? $default : (int) $value;
    }

    /** Скидає пам'ять — потрібно після збереження в адмінці. */
    public static function flush(): void
    {
        self::$cache = null;
    }
}
