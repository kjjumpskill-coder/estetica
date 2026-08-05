<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

/**
 * На проді відвідувач бачить нейтральну сторінку, деталі йдуть у лог.
 * Локально — навпаки, деталі на екран, бо інакше налагодження неможливе.
 */
final class ErrorHandler
{
    public static function register(): void
    {
        $debug = Config::bool('APP_DEBUG', false) && !Config::isProduction();

        ini_set('display_errors', $debug ? '1' : '0');
        error_reporting(E_ALL);

        set_exception_handler(static function (Throwable $e) use ($debug): void {
            Logger::error(
                get_class($e) . ': ' . $e->getMessage(),
                ['file' => $e->getFile() . ':' . $e->getLine()]
            );

            if (headers_sent() === false) {
                http_response_code(500);
                header('Content-Type: text/html; charset=utf-8');
            }

            if ($debug) {
                printf(
                    "<pre style=\"padding:24px;font:14px/1.6 ui-monospace,monospace\">%s: %s\n%s:%d\n\n%s</pre>",
                    htmlspecialchars(get_class($e), ENT_QUOTES),
                    htmlspecialchars($e->getMessage(), ENT_QUOTES),
                    htmlspecialchars($e->getFile(), ENT_QUOTES),
                    $e->getLine(),
                    htmlspecialchars($e->getTraceAsString(), ENT_QUOTES)
                );

                return;
            }

            echo '<!doctype html><html lang="uk"><meta charset="utf-8">'
                . '<title>Технічна помилка</title>'
                . '<body style="font:16px/1.6 system-ui;padding:48px;color:#2E2A27;background:#FBF8F5">'
                . '<h1 style="font-weight:400">Сталася технічна помилка</h1>'
                . '<p>Ми вже знаємо про неї. Спробуйте, будь ласка, оновити сторінку за хвилину.</p>'
                . '</body></html>';
        });

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if ((error_reporting() & $severity) === 0) {
                return false;
            }

            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
    }
}
