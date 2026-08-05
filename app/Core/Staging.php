<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Захист тестового майданчика.
 *
 * На піддомені лежить той самий сайт із реальними фото клієнток і текстами
 * відгуків. Тому середовище, яке не є production, закривається двічі:
 *
 *   1. Пошуковикам — заголовок X-Robots-Tag, метатег і robots.txt, що забороняє все.
 *      Одного robots.txt мало: він лише просить не індексувати, і сторінка, на яку
 *      десь є посилання, все одно може потрапити у видачу.
 *   2. Людям — HTTP Basic, якщо в .env заданий STAGING_PASS. Без пароля майданчик
 *      просто відкритий за посиланням: це прийнятно для узгодження верстки,
 *      але не для сайту з чужими обличчями.
 *
 * На production не робить нічого.
 */
final class Staging
{
    public static function isActive(): bool
    {
        return !Config::isProduction();
    }

    /** Викликається у front controller до будь-якого виводу. */
    public static function protect(): void
    {
        if (!self::isActive()) {
            return;
        }

        header('X-Robots-Tag: noindex, nofollow, noarchive', true);

        $expected = Config::str('STAGING_PASS');

        if ($expected === '') {
            return;
        }

        $user = $_SERVER['PHP_AUTH_USER'] ?? '';
        $pass = $_SERVER['PHP_AUTH_PW'] ?? '';

        $userOk = hash_equals(Config::str('STAGING_USER', 'estetika'), $user);
        $passOk = hash_equals($expected, $pass);

        if ($userOk && $passOk) {
            return;
        }

        header('WWW-Authenticate: Basic realm="Estetika — тестовий майданчик", charset="UTF-8"');
        http_response_code(401);
        header('Content-Type: text/html; charset=utf-8');

        echo '<!doctype html><html lang="uk"><meta charset="utf-8">'
            . '<title>Тестовий майданчик</title>'
            . '<body style="font:16px/1.6 system-ui;padding:48px;color:#2E2A27;background:#FBF8F5">'
            . '<h1 style="font-weight:400">Сайт ще готується</h1>'
            . '<p>Це закритий майданчик для узгодження. Доступ — за паролем від розробника.</p>'
            . '</body></html>';

        exit;
    }

    /**
     * Віддає статичний файл засобами PHP.
     *
     * Потрібно лише на тестовому майданчику: там Apache перенаправляє /media та
     * /assets у front controller, щоб фото клієнток не можна було відкрити за
     * прямим посиланням в обхід пароля. На production цей код не виконується —
     * статику віддає веб-сервер, як і має бути.
     *
     * @return bool чи вдалося знайти файл
     */
    public static function serveStatic(string $path): bool
    {
        $root = BASE_PATH . '/public_html';
        $full = realpath($root . '/' . ltrim($path, '/'));

        // realpath + перевірка префікса: без цього ../../ вивів би за межі webroot.
        if ($full === false || !str_starts_with($full, $root . DIRECTORY_SEPARATOR) || !is_file($full)) {
            return false;
        }

        $types = [
            'webp' => 'image/webp',  'jpg'  => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'png'  => 'image/png',   'svg'  => 'image/svg+xml',
            'css'  => 'text/css',    'js'   => 'text/javascript',
            'woff2' => 'font/woff2', 'ico'  => 'image/x-icon',
        ];

        $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));

        header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
        header('Content-Length: ' . filesize($full));
        header('Cache-Control: private, max-age=0, no-store');

        readfile($full);

        return true;
    }

    /** Вміст robots.txt для поточного середовища. */
    public static function robots(string $appUrl): string
    {
        if (self::isActive()) {
            return "# Тестовий майданчик. Індексація заборонена повністю.\nUser-agent: *\nDisallow: /\n";
        }

        return "User-agent: *\nAllow: /\n\n"
            . ($appUrl === '' ? '' : "Sitemap: {$appUrl}/sitemap.xml\n");
    }
}
