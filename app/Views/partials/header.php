<?php $navItems = require __DIR__ . '/nav-items.php'; ?>
<header class="header" data-header>
    <div class="wrap header__inner">
        <a class="header__logo" href="/" aria-label="<?= e(setting('brand_name', 'Estetika')) ?> — на головну">
            <span style="color:var(--c-accent)"><?php $this->partial('logo') ?></span>
            <span class="header__wordmark"><?= e(setting('brand_name', 'Estetika')) ?></span>
        </a>

        <nav class="nav" aria-label="Основне меню">
            <?php foreach ($navItems as $href => $label): ?>
                <a href="<?= e($href) ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>

        <div style="display:flex;gap:.5rem;align-items:center">
            <a class="btn btn--primary header__cta" href="#zapys">Записатись</a>
            <button class="burger" type="button" data-nav-open aria-label="Відкрити меню" aria-expanded="false">
                <span></span>
            </button>
        </div>
    </div>
</header>
