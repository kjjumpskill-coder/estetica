<?php

declare(strict_types=1);

use App\Repositories\SettingsRepository;

/**
 * Кілька коротких функцій для шаблонів. Без них кожен виклик у верстці
 * перетворюється на SettingsRepository::get('phone'), і розмітку стає важко читати.
 */

if (!function_exists('setting')) {
    function setting(string $key, string $default = ''): string
    {
        return SettingsRepository::get($key, $default);
    }
}

if (!function_exists('has_setting')) {
    /** Чи заповнене значення. Керує тим, показувати блок чи ховати його цілком. */
    function has_setting(string $key): bool
    {
        return SettingsRepository::has($key);
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('paragraphs')) {
    /**
     * Текст із бази зберігається з \n\n між абзацами. Тут він стає <p>,
     * з екрануванням кожного абзацу окремо.
     */
    function paragraphs(?string $text): string
    {
        $text = trim((string) $text);

        if ($text === '') {
            return '';
        }

        $parts = preg_split('/\n\s*\n/u', $text) ?: [];

        return implode('', array_map(
            static fn(string $p): string => '<p>' . nl2br(e(trim($p))) . '</p>',
            $parts
        ));
    }
}

if (!function_exists('phone_digits')) {
    /** Телефон для href="tel:" — лише цифри й плюс. */
    function phone_digits(string $phone): string
    {
        return preg_replace('/[^\d+]/', '', $phone) ?? '';
    }
}
