<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Мінімальний markdown для блогу.
 *
 * Підтримується рівно те, що потрібно статтям: заголовки другого й третього
 * рівня, абзаци, списки та жирний текст. Повноцінний парсер сюди тягнути немає
 * сенсу — це зайва залежність заради розмітки, яку пише одна людина.
 *
 * Вхідний текст екранується ПЕРЕД розбором. Тому навіть якщо в адмінці хтось
 * вставить у статтю тег, він виведеться як текст, а не виконається.
 */
final class Markdown
{
    public static function toHtml(string $text): string
    {
        $text = str_replace("\r\n", "\n", trim($text));

        if ($text === '') {
            return '';
        }

        $html = [];

        foreach (preg_split('/\n\s*\n/u', $text) ?: [] as $block) {
            $block = trim($block);

            if ($block === '') {
                continue;
            }

            $html[] = self::renderBlock($block);
        }

        return implode("\n", $html);
    }

    private static function renderBlock(string $block): string
    {
        // Список: усі рядки блоку починаються з дефіса.
        $lines = explode("\n", $block);
        $isList = true;

        foreach ($lines as $line) {
            if (!str_starts_with(trim($line), '- ')) {
                $isList = false;
                break;
            }
        }

        if ($isList) {
            $items = array_map(
                static fn(string $l): string => '<li>' . self::inline(mb_substr(trim($l), 2)) . '</li>',
                $lines
            );

            return '<ul>' . implode('', $items) . '</ul>';
        }

        if (str_starts_with($block, '### ')) {
            return '<h3>' . self::inline(mb_substr($block, 4)) . '</h3>';
        }

        if (str_starts_with($block, '## ')) {
            return '<h2>' . self::inline(mb_substr($block, 3)) . '</h2>';
        }

        return '<p>' . self::inline($block) . '</p>';
    }

    private static function inline(string $text): string
    {
        $safe = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // Переносимо одиночні розриви рядків усередині абзацу.
        $safe = nl2br($safe, false);

        return preg_replace('/\*\*(.+?)\*\*/su', '<strong>$1</strong>', $safe) ?? $safe;
    }
}
