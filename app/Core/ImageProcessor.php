<?php

declare(strict_types=1);

namespace App\Core;

use GdImage;
use RuntimeException;

/**
 * Генерація WebP-похідних із вихідного фото.
 *
 * Працює на чистому GD, без intervention/image: на shared-хостингу кожна зайва
 * залежність — це і вага, і ризик, що після оновлення PHP щось відвалиться.
 *
 * Метадані не переносяться взагалі. Це не оптимізація, а вимога приватності:
 * фото з телефону містять GPS-координати місця зйомки, тобто адресу кабінету
 * або, гірше, домашню адресу клієнтки. imagewebp() пише лише піксельні дані,
 * тому EXIF зникає сам собою — але орієнтацію з нього треба прочитати ДО того,
 * інакше половина знімків ляже набік.
 */
final class ImageProcessor
{
    public const SIZES   = [480, 960, 1440, 1920];
    public const QUALITY = 78;
    public const LQIP_WIDTH = 20;

    /**
     * @return array{width:int,height:int,lqip:string}
     */
    public function generate(string $sourcePath, string $targetDir, string $name, int $extraRotation = 0): array
    {
        $image = $this->load($sourcePath);
        $image = $this->applyExifOrientation($image, $sourcePath);

        if ($extraRotation !== 0) {
            $rotated = imagerotate($image, -$extraRotation, 0);

            if ($rotated !== false) {
                imagedestroy($image);
                $image = $rotated;
            }
        }

        // Портрети з дзеркалки — це 4000×6000, тобто ~96 МБ у пам'яті на один знімок.
        // Найбільший потрібний нам розмір — 1920, тому одразу зводимо джерело до нього
        // і звільняємо оригінал. Інакше пакетний імпорт впирається в memory_limit
        // рівно на третьому фото, і це станеться так само на хостингу.
        $image = $this->downscaleToWorkingSize($image);

        $width  = imagesx($image);
        $height = imagesy($image);

        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            throw new RuntimeException("Не вдалося створити теку {$targetDir}");
        }

        foreach (self::SIZES as $size) {
            // Дрібні фото не апскейлимо — це лише роздує файл без користі для якості.
            $targetWidth = min($size, $width);
            $resized = $this->resize($image, $targetWidth);

            imagewebp($resized, sprintf('%s/%s-%d.webp', $targetDir, $name, $size), self::QUALITY);
            imagedestroy($resized);
        }

        $lqip = $this->makeLqip($image);

        imagedestroy($image);

        return ['width' => $width, 'height' => $height, 'lqip' => $lqip];
    }

    private function downscaleToWorkingSize(GdImage $image): GdImage
    {
        $max = max(self::SIZES);

        if (imagesx($image) <= $max) {
            return $image;
        }

        $working = $this->resize($image, $max);
        imagedestroy($image);

        return $working;
    }

    private function load(string $path): GdImage
    {
        $info = @getimagesize($path);

        if ($info === false) {
            throw new RuntimeException("Не вдалося прочитати зображення: {$path}");
        }

        $image = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            default        => false,
        };

        if ($image === false) {
            throw new RuntimeException(
                'Непідтримуваний формат: ' . ($info['mime'] ?? '?')
                . '. HEIC потрібно спершу пропустити через bin/heic-convert.sh'
            );
        }

        // PNG зі скріншотів мають альфу; на молочному фоні прозорість дала б чорний.
        if ($info[2] === IMAGETYPE_PNG) {
            $image = $this->flattenAlpha($image);
        }

        return $image;
    }

    private function flattenAlpha(GdImage $image): GdImage
    {
        $flat = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefill($flat, 0, 0, imagecolorallocate($flat, 255, 255, 255));
        imagecopy($flat, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        imagedestroy($image);

        return $flat;
    }

    /** Повертає фото за EXIF до того, як метадані будуть відкинуті. */
    private function applyExifOrientation(GdImage $image, string $path): GdImage
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $orientation = (int) ($exif['Orientation'] ?? 1);

        $angle = match ($orientation) {
            3       => 180,
            6       => -90,
            8       => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        if ($rotated === false) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }

    private function resize(GdImage $image, int $targetWidth): GdImage
    {
        $width  = imagesx($image);
        $height = imagesy($image);
        $targetHeight = max(1, (int) round($height * ($targetWidth / $width)));

        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        return $resized;
    }

    /** Base64 WebP 20px завширшки — заглушка, поки вантажиться справжнє фото. */
    private function makeLqip(GdImage $image): string
    {
        $tiny = $this->resize($image, self::LQIP_WIDTH);

        ob_start();
        imagewebp($tiny, null, 40);
        $data = (string) ob_get_clean();

        imagedestroy($tiny);

        return 'data:image/webp;base64,' . base64_encode($data);
    }
}
