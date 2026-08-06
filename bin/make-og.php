<?php

declare(strict_types=1);

/**
 * Генерує картку для прев'ю посилань (Open Graph), 1200×630.
 *
 *   php bin/make-og.php
 *
 * Навіщо окремий файл: усі фото майстра вертикальні, а соцмережі й месенджери
 * очікують горизонтальну картку 1,91:1. Будь-який автоматичний кроп портрета
 * у цю пропорцію дає торс без обличчя. Тому картка збирається: молочне тло,
 * тонка золота рамка, назва бренду й портрет праворуч, обрізаний по обличчю.
 *
 * TTF-шрифти лежать у storage/fonts і потрібні лише тут — на сайті працюють
 * ті самі гарнітури у форматі woff2.
 */

if (PHP_SAPI !== 'cli') {
    exit("Тільки з командного рядка.\n");
}

require dirname(__DIR__) . '/app/bootstrap.php';

ini_set('memory_limit', '512M');

const W = 1200;
const H = 630;

const SOURCE = BASE_PATH . '/storage/originals/master/IMG_0505.JPG';
const TARGET = BASE_PATH . '/public_html/assets/img/og-cover.jpg';

$playfair = BASE_PATH . '/storage/fonts/playfair.ttf';
$manrope  = BASE_PATH . '/storage/fonts/manrope.ttf';

foreach ([SOURCE, $playfair, $manrope] as $file) {
    if (!is_file($file)) {
        exit("Немає файлу: {$file}\n");
    }
}

$canvas = imagecreatetruecolor(W, H);

$hex = static fn(string $h): array => [
    (int) hexdec(substr($h, 0, 2)),
    (int) hexdec(substr($h, 2, 2)),
    (int) hexdec(substr($h, 4, 2)),
];

[$br, $bg, $bb] = $hex('FBF8F5');   // молочний фон
[$ar, $ag, $ab] = $hex('B08D57');   // золото
[$ir, $ig, $ib] = $hex('2E2A27');   // основний текст
[$mr, $mg, $mb] = $hex('857A70');   // другорядний

$colBg     = imagecolorallocate($canvas, $br, $bg, $bb);
$colAccent = imagecolorallocate($canvas, $ar, $ag, $ab);
$colInk    = imagecolorallocate($canvas, $ir, $ig, $ib);
$colMuted  = imagecolorallocate($canvas, $mr, $mg, $mb);

imagefilledrectangle($canvas, 0, 0, W, H, $colBg);

// ---- Портрет праворуч ----
//
// Голова на вихідному кадрі 4000×6000 припадає приблизно на верхню третину.
// Беремо вертикальну смугу навколо неї, а не центр кадру: центр — це коліна.
$photo = imagecreatefromjpeg(SOURCE);

$srcW = imagesx($photo);
$srcH = imagesy($photo);

$panelW = 470;
$cropH  = (int) ($srcH * 0.62);
$cropW  = (int) ($cropH * ($panelW / H));
$cropX  = (int) (($srcW - $cropW) / 2);
$cropY  = (int) ($srcH * 0.10);

$panel = imagecreatetruecolor($panelW, H);
imagecopyresampled($panel, $photo, 0, 0, $cropX, $cropY, $panelW, H, $cropW, $cropH);
imagedestroy($photo);

imagecopy($canvas, $panel, W - $panelW, 0, 0, 0, $panelW, H);
imagedestroy($panel);

// ---- Тонка золота рамка ----
imagesetthickness($canvas, 1);
imagerectangle($canvas, 28, 28, W - 29, H - 29, $colAccent);

// ---- Текст ----
$textX = 78;

// Надзаголовок
imagettftext($canvas, 15, 0, $textX, 150, $colAccent, $manrope, 'ESTETIKA · ДНІПРО');

// Головна фраза, двома рядками
imagettftext($canvas, 52, 0, $textX, 265, $colInk, $playfair, 'Я вже бачу вас');
imagettftext($canvas, 52, 0, $textX, 340, $colAccent, $playfair, 'красивими');

// Ім'я та спеціалізація
imagettftext($canvas, 21, 0, $textX, 435, $colInk, $playfair, 'Ольга Кірілова');
imagettftext($canvas, 15, 0, $textX, 475, $colMuted, $manrope, 'Косметолог-естетист,');
imagettftext($canvas, 15, 0, $textX, 502, $colMuted, $manrope, 'майстер перманентного макіяжу');

// Розділова риска й підпис знизу
imageline($canvas, $textX, 540, $textX + 44, 540, $colAccent);
imagettftext($canvas, 14, 0, $textX, 578, $colMuted, $manrope, '20+ років практики · 5 000 клієнток');

imagejpeg($canvas, TARGET, 88);
imagedestroy($canvas);

printf("Готово: %s (%d КБ)\n", str_replace(BASE_PATH . '/', '', TARGET), (int) (filesize(TARGET) / 1024));
