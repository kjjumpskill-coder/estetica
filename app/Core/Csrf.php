<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    private const KEY = '_csrf';

    public static function token(): string
    {
        Session::start();

        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::KEY];
    }

    public static function check(?string $token): bool
    {
        Session::start();
        $expected = $_SESSION[self::KEY] ?? null;

        if (!is_string($expected) || !is_string($token) || $token === '') {
            return false;
        }

        return hash_equals($expected, $token);
    }

    public static function field(): string
    {
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            self::KEY,
            htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8')
        );
    }

    public static function fieldName(): string
    {
        return self::KEY;
    }
}
