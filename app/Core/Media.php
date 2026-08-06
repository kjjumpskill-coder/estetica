<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Збирає розмітку <picture> для зображень, згенерованих пайплайном імпорту.
 *
 * У базі лежить path_base без суфікса розміру («media/works/abc123»), а на диску —
 * чотири файли: abc123-480.webp … abc123-1920.webp. Явні width/height обов'язкові:
 * без них браузер не резервує місце під картинку, і сторінка стрибає при завантаженні.
 */
final class Media
{
    public const SIZES = [480, 960, 1440, 1920];

    /**
     * @param array<string,mixed> $media поля path_base, width, height, lqip, alt
     * @param string $sizes значення атрибута sizes
     */
    public static function picture(
        array $media,
        string $alt = '',
        string $sizes = '100vw',
        bool $lazy = true,
        string $class = ''
    ): string {
        $base = (string) ($media['path_base'] ?? '');

        if ($base === '') {
            return '';
        }

        $width  = (int) ($media['width'] ?? 0);
        $height = (int) ($media['height'] ?? 0);
        $alt    = $alt !== '' ? $alt : (string) ($media['alt'] ?? '');
        $lqip   = (string) ($media['lqip'] ?? '');

        $srcset = implode(', ', array_map(
            static fn(int $w): string => sprintf('/%s-%d.webp %dw', $base, $w, $w),
            self::SIZES
        ));

        $style = $lqip !== ''
            ? sprintf(' style="background-image:url(%s);background-size:cover"', self::esc($lqip))
            : '';

        return sprintf(
            '<img src="/%s-960.webp" srcset="%s" sizes="%s" width="%d" height="%d" alt="%s"%s%s%s%s decoding="async">',
            self::esc($base),
            self::esc($srcset),
            self::esc($sizes),
            $width,
            $height,
            self::esc($alt),
            $class !== '' ? ' class="' . self::esc($class) . '"' : '',
            $lazy ? ' loading="lazy"' : ' fetchpriority="high"',
            // Позначка для плавного проявлення з розмитої заглушки.
            // Ставиться лише там, де заглушка справді є.
            $lqip !== '' ? ' data-lqip' : '',
            $style
        );
    }

    /** URL конкретного розміру — для og:image і preload. */
    public static function url(array $media, int $size = 1440): string
    {
        $base = (string) ($media['path_base'] ?? '');

        return $base === '' ? '' : sprintf('/%s-%d.webp', $base, $size);
    }

    private static function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
