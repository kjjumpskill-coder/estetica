<?php

declare(strict_types=1);

/**
 * Масовий імпорт фото зі storage/originals.
 *
 *   php bin/import-photos.php            імпортувати нове
 *   php bin/import-photos.php --dry-run  показати, що буде зроблено
 *
 * Скрипт ідемпотентний: повторний запуск нічого не дублює й нічого не ламає.
 * Дедуп іде по SHA-1 вмісту файлу, а не по імені — тому «IMG_1462 — копия.JPG»
 * буде пропущена як дублікат «IMG_1462.JPG».
 *
 * Усе імпортується з is_published = 0. Що саме показувати на сайті, вирішує
 * власниця в адмінці — скрипт не публікує нічого самостійно.
 *
 * Пари «до/після» визначаються за іменем: 001-before.jpg + 001-after.jpg.
 * Файл без пари стає одиночним записом. Фото, зняте вже як готовий колаж
 * (звична практика в інстаграмі), теж імпортується одиночним — воно і є
 * закінченим зображенням.
 */

if (PHP_SAPI !== 'cli') {
    exit("Тільки з командного рядка.\n");
}

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Db;
use App\Core\ImageProcessor;

// Один кадр із дзеркалки розпакований у пам'ять — це десятки мегабайт. У CLI ліміт
// підняти можна й треба; через адмінку діє інше обмеження — не більше 10 фото за раз.
ini_set('memory_limit', '512M');
set_time_limit(0);

$dryRun = in_array('--dry-run', $argv, true);

const CATEGORY_DIRS = [
    'works'    => 'works',
    'reviews'  => 'reviews',
    'diplomas' => 'diplomas',
    'studio'   => 'studio',
    'master'   => 'master',
];

/**
 * Підпапка всередині works/ визначає послугу — саме для цього вона й потрібна.
 * Файл, покладений у корінь works/, імпортується без прив'язки: власниця
 * призначить послугу в адмінці.
 */
const WORK_DIR_SERVICE = [
    'brows'             => 'brovy',
    'lips'              => 'guby',
    'eyeliner'          => 'strilky',
    'interlash'         => 'mizhviykovyi-prostir',
    'lips-filler'       => 'konturna-plastyka-gub',
    'botox'             => 'botulinoterapiya',
    'lifting'           => 'lifting-procedury',
    'lipolytics'        => 'lipolitychni-inyekciyi',
    'mesotherapy'       => 'mezoterapiya',
    'biorevitalization' => 'biorevitalizaciya',
    'polynucleotides'   => 'polinukleotydy',
    'injections'        => null,
];

$originals = BASE_PATH . '/storage/originals';
$mediaRoot = BASE_PATH . '/public_html/media';
$processor = new ImageProcessor();

$stats = ['imported' => 0, 'skipped' => 0, 'failed' => 0, 'unpaired' => []];

foreach (CATEGORY_DIRS as $category => $dir) {
    $path = $originals . '/' . $dir;

    if (!is_dir($path)) {
        continue;
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        /** @var SplFileInfo $file */
        if (!$file->isFile()) {
            continue;
        }

        // _heic — куди heic-convert.sh відкладає вихідні файли після конвертації.
        if (str_contains($file->getPathname(), '/_heic/')) {
            continue;
        }

        $ext = strtolower($file->getExtension());

        if (in_array($ext, ['heic', 'heif'], true)) {
            printf("  ! %s — HEIC. Спершу запустіть bin/heic-convert.sh\n", $file->getFilename());
            $stats['failed']++;
            continue;
        }

        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            continue;
        }

        $files[] = $file->getPathname();
    }

    sort($files, SORT_NATURAL);

    printf("\n[%s] файлів до розгляду: %d\n", $category, count($files));

    $importedIds = [];

    foreach ($files as $file) {
        $sha1 = sha1_file($file);

        if ($sha1 === false) {
            $stats['failed']++;
            continue;
        }

        $existing = Db::selectOne('SELECT id FROM media WHERE sha1 = ?', [$sha1]);

        if ($existing !== null) {
            $stats['skipped']++;
            $importedIds[$file] = (int) $existing['id'];
            continue;
        }

        if ($dryRun) {
            printf("  + %s\n", basename($file));
            $stats['imported']++;
            continue;
        }

        try {
            $name = substr($sha1, 0, 16);
            $result = $processor->generate($file, $mediaRoot . '/' . $category, $name);

            $id = Db::insert(
                'INSERT INTO media (category, path_base, width, height, lqip, sha1, alt)
                 VALUES (?, ?, ?, ?, ?, ?, ?)',
                [
                    $category,
                    'media/' . $category . '/' . $name,
                    $result['width'],
                    $result['height'],
                    $result['lqip'],
                    $sha1,
                    '',
                ]
            );

            $importedIds[$file] = $id;
            $stats['imported']++;
            printf("  + %s → media/%s/%s\n", basename($file), $category, $name);
        } catch (Throwable $e) {
            printf("  ПОМИЛКА %s: %s\n", basename($file), $e->getMessage());
            $stats['failed']++;
        }
    }

    if ($category === 'works' && !$dryRun) {
        linkWorks($importedIds, $stats);
    }

    if ($category === 'diplomas' && !$dryRun) {
        linkDiplomas($importedIds);
    }
}

printf(
    "\n----\nІмпортовано: %d\nПропущено (вже в базі): %d\nПомилок: %d\n",
    $stats['imported'],
    $stats['skipped'],
    $stats['failed']
);

if ($stats['unpaired'] !== []) {
    printf("\nФайли без пари (імпортовані одиночними): %s\n", implode(', ', $stats['unpaired']));
}

if (!$dryRun) {
    echo "\nУсе імпортовано з is_published = 0. Опублікуйте потрібне в адмінці.\n";
    \App\Core\PageCache::invalidate();
}

/**
 * Створює записи works. Пара визначається спільним номером у імені:
 * 001-before.jpg та 001-after.jpg. Решта стає одиночними записами.
 * Послуга береться з підпапки, у якій лежав файл.
 *
 * @param array<string,int> $mediaIds повний шлях до файлу → id у media
 */
function linkWorks(array $mediaIds, array &$stats): void
{
    $pairs = [];
    $singles = [];

    foreach ($mediaIds as $path => $mediaId) {
        $filename = basename($path);

        if (preg_match('/^(.+?)-(before|after)\.[a-z]+$/i', $filename, $m) === 1) {
            $pairs[dirname($path) . '/' . $m[1]][strtolower($m[2])] = $mediaId;
        } else {
            $singles[$path] = $mediaId;
        }
    }

    foreach ($pairs as $key => $sides) {
        $serviceId = serviceIdForPath($key);

        if (!isset($sides['after'])) {
            // «before» без «after» показувати нема сенсу — це фото проблеми без рішення.
            $stats['unpaired'][] = basename($key) . '-before';
            continue;
        }

        if (!isset($sides['before'])) {
            $stats['unpaired'][] = basename($key) . '-after';
            insertWork($sides['after'], null, 'single', $serviceId);
            continue;
        }

        insertWork($sides['after'], $sides['before'], 'pair', $serviceId);
    }

    foreach ($singles as $path => $mediaId) {
        // Знімок без пари в імені — найчастіше готовий колаж «до/після» одним кадром.
        insertWork($mediaId, null, 'collage', serviceIdForPath($path));
    }
}

/** Послуга за назвою підпапки. null, якщо файл лежить у корені works/. */
function serviceIdForPath(string $path): ?int
{
    static $cache = [];

    $dir = basename(dirname($path));
    $slug = WORK_DIR_SERVICE[$dir] ?? null;

    if ($slug === null) {
        return null;
    }

    if (!array_key_exists($slug, $cache)) {
        $id = Db::scalar('SELECT id FROM services WHERE slug = ?', [$slug]);
        $cache[$slug] = $id === null ? null : (int) $id;
    }

    return $cache[$slug];
}

function insertWork(int $afterId, ?int $beforeId, string $layout, ?int $serviceId): void
{
    $exists = Db::scalar('SELECT id FROM works WHERE after_media_id = ?', [$afterId]);

    if ($exists !== null) {
        return;
    }

    Db::insert(
        'INSERT INTO works (layout, before_media_id, after_media_id, service_id, is_published)
         VALUES (?, ?, ?, ?, 0)',
        [$layout, $beforeId, $afterId, $serviceId]
    );
}

/** @param array<string,int> $mediaIds */
function linkDiplomas(array $mediaIds): void
{
    foreach ($mediaIds as $mediaId) {
        $exists = Db::scalar('SELECT id FROM diplomas WHERE media_id = ?', [$mediaId]);

        if ($exists !== null) {
            continue;
        }

        Db::insert(
            'INSERT INTO diplomas (title, media_id, is_published) VALUES (?, ?, 0)',
            ['', $mediaId]
        );
    }
}
