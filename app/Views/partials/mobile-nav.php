<?php $navItems = require __DIR__ . '/nav-items.php'; ?>
<div class="mobile-nav" data-nav-panel data-open="false" role="dialog" aria-modal="true" aria-label="Меню">
    <div class="mobile-nav__top">
        <button class="icon-btn" type="button" data-nav-close aria-label="Закрити меню">&times;</button>
    </div>

    <?php foreach ($navItems as $href => $label): ?>
        <a href="<?= e($href) ?>" data-nav-link><?= e($label) ?></a>
    <?php endforeach; ?>

    <a class="btn btn--primary" href="#zapys" data-nav-link>Записатись</a>

    <?php if (has_setting('phone')): ?>
        <a class="btn btn--ghost" href="tel:<?= e(phone_digits(setting('phone'))) ?>" data-event="phone_click">
            <?= e(setting('phone')) ?>
        </a>
    <?php endif; ?>
</div>
