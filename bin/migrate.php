<?php

declare(strict_types=1);

/**
 * Runner міграцій.
 *
 *   php bin/migrate.php          застосувати нові
 *   php bin/migrate.php --status показати, що застосовано
 *
 * Кожен .sql із /migrations виконується один раз; факт застосування пишеться
 * в таблицю schema_migrations. Повторний запуск нічого не робить.
 */

if (PHP_SAPI !== 'cli') {
    exit("Тільки з командного рядка.\n");
}

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Db;

$pdo = Db::pdo();

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS schema_migrations (
        filename    VARCHAR(160) NOT NULL PRIMARY KEY,
        applied_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);

$applied = array_column(
    Db::select('SELECT filename FROM schema_migrations ORDER BY filename'),
    'filename'
);

$files = glob(BASE_PATH . '/migrations/*.sql') ?: [];
sort($files, SORT_STRING);

if (in_array('--status', $argv, true)) {
    foreach ($files as $file) {
        $name = basename($file);
        printf("%s %s\n", in_array($name, $applied, true) ? '  застосовано' : '  очікує    ', $name);
    }
    exit(0);
}

$pending = array_values(array_filter(
    $files,
    static fn(string $f): bool => !in_array(basename($f), $applied, true)
));

if ($pending === []) {
    echo "Нових міграцій немає.\n";
    exit(0);
}

foreach ($pending as $file) {
    $name = basename($file);
    $sql  = file_get_contents($file);

    if ($sql === false || trim($sql) === '') {
        printf("  пропущено (порожній): %s\n", $name);
        continue;
    }

    try {
        // DDL у MySQL не відкочується транзакцією, тому кожна міграція має бути
        // самодостатньою: або весь файл лягає, або його правлять і ганяють знову.
        $pdo->exec($sql);
        Db::execute('INSERT INTO schema_migrations (filename) VALUES (?)', [$name]);
        printf("  ок: %s\n", $name);
    } catch (\PDOException $e) {
        printf("  ПОМИЛКА в %s: %s\n", $name, $e->getMessage());
        exit(1);
    }
}

printf("\nЗастосовано міграцій: %d\n", count($pending));
