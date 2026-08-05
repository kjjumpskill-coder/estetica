<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Тонка обгортка над PDO. Єдиний спосіб потрапити в базу — через ці методи,
 * і всі вони приймають параметри окремо від SQL. Конкатенації значень у запит
 * не існує як можливості.
 */
final class Db
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            Config::str('DB_HOST', '127.0.0.1'),
            Config::int('DB_PORT', 3306),
            Config::str('DB_NAME'),
            Config::str('DB_CHARSET', 'utf8mb4')
        );

        try {
            self::$pdo = new PDO($dsn, Config::str('DB_USER'), Config::str('DB_PASS'), [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Повідомлення PDO містить креденшели — назовні воно піти не має.
            Logger::error('Не вдалося підключитися до бази: ' . $e->getMessage());
            throw new RuntimeException('Немає з\'єднання з базою даних', 0, $e);
        }

        return self::$pdo;
    }

    /** @return array<int,array<string,mixed>> */
    public static function select(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public static function selectOne(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();

        return $row === false ? null : $row;
    }

    public static function scalar(string $sql, array $params = []): mixed
    {
        $value = self::run($sql, $params)->fetchColumn();

        return $value === false ? null : $value;
    }

    /** Кількість зачеплених рядків. */
    public static function execute(string $sql, array $params = []): int
    {
        return self::run($sql, $params)->rowCount();
    }

    /** id щойно вставленого рядка. */
    public static function insert(string $sql, array $params = []): int
    {
        self::run($sql, $params);

        return (int) self::pdo()->lastInsertId();
    }

    public static function transaction(callable $fn): mixed
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();

        try {
            $result = $fn();
            $pdo->commit();

            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();

            throw $e;
        }
    }

    private static function run(string $sql, array $params): \PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }
}
