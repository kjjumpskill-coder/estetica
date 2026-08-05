<?php
/**
 * @var string $content
 * @var string|null $pageTitle
 * @var bool|null $noindex
 */
$brand    = setting('brand_name', 'Estetika');
$master   = setting('master_name');
$city     = setting('city', 'Дніпро');
$cityIn   = setting('city_locative', 'Дніпрі');
$title    = $pageTitle ?? null;
$fullTitle = $title !== null
    ? $title . ' — ' . $brand
    : $master . ' — перманентний макіяж та косметологія у ' . $cityIn . ' | ' . $brand;
$description = $description
    ?? 'Перманентний макіяж, ін’єкційна косметологія та догляд у ' . $cityIn . '. '
     . 'Понад 20 років практики, 5 000 клієнток, призерка чемпіонатів України. Запис онлайн.';
$appUrl = rtrim((string) \App\Core\Config::str('APP_URL', ''), '/');
$path   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
?>
<!doctype html>
<html lang="uk">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php
// Ставимо .js до першого малювання. Анімації появи ховають елементи через цей клас,
// тому без нього сторінка лишається повністю читабельною навіть якщо JS не завантажився.
// Інлайном і в <head> — інакше буде видно спалах прихованого контенту.
?>
<script>document.documentElement.classList.add('js')</script>
<title><?= e($fullTitle) ?></title>
<meta name="description" content="<?= e($description) ?>">
<?php // На тестовому майданчику noindex стоїть завжди, незалежно від сторінки. ?>
<?php if (\App\Core\Staging::isActive()): ?>
    <meta name="robots" content="noindex, nofollow, noarchive">
<?php elseif (!empty($noindex)): ?>
    <meta name="robots" content="noindex, follow">
<?php endif; ?>
<link rel="canonical" href="<?= e($appUrl . $path) ?>">

<meta property="og:type" content="website">
<meta property="og:locale" content="uk_UA">
<meta property="og:site_name" content="<?= e($brand) ?>">
<meta property="og:title" content="<?= e($fullTitle) ?>">
<meta property="og:description" content="<?= e($description) ?>">
<?php if (!empty($ogImage)): ?>
    <meta property="og:image" content="<?= e($appUrl . $ogImage) ?>">
<?php endif; ?>

<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">

<?php
// Кирилиця тягнеться на кожній сторінці — її вантажимо наперед.
// Латиниця потрібна теж (у ній апостроф ’), але вона рідша в потоці тексту,
// тому браузер підтягне її сам за unicode-range.
?>
<link rel="preload" href="/assets/fonts/manrope-cyrillic.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="/assets/fonts/playfair-cyrillic.woff2" as="font" type="font/woff2" crossorigin>

<?php // Критичний CSS інлайном: перший екран малюється без жодного зовнішнього запиту. ?>
<style><?= file_get_contents(BASE_PATH . '/public_html/assets/css/critical.css') ?></style>

<link rel="stylesheet" href="/assets/css/main.css" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="/assets/css/main.css"></noscript>

<script type="module" src="/assets/js/app.js" defer></script>
</head>
<body>

<a class="skip-link" href="#main">Перейти до основного вмісту</a>

<?php $this->partial('header') ?>

<main id="main">
    <?= $this->raw($content) ?>
</main>

<?php $this->partial('footer') ?>
<?php $this->partial('mobile-nav') ?>
<?php $this->partial('modals') ?>

</body>
</html>
