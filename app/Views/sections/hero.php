<?php
/**
 * Hero.
 *
 * Головна обіцянка тут — не рекламний слоган, а власна фраза майстра:
 * «Я вже бачу вас красивими». Вона одночасно знімає головне заперечення
 * («вийде неприродно, втрачу себе») і задає тон усій сторінці, тому набрана
 * як цитата, а не як заголовок продукту.
 *
 * @var array<string,mixed>|null $master
 */
?>
<section class="hero">
    <div class="wrap hero__grid">
        <div>
            <p class="hero__brand"><?= e(setting('brand_name', 'Estetika')) ?> · <?= e(setting('city', 'Дніпро')) ?></p>

            <h1 class="hero__title">Я вже бачу вас <em>красивими</em></h1>

            <p class="hero__lead"><?= e(setting('hero_subtitle')) ?></p>

            <div class="hero__actions">
                <a class="btn btn--primary" href="#zapys" data-event="cta_hero">Записатись</a>
                <a class="btn btn--ghost" href="#zapys" data-event="cta_consult">Безкоштовна консультація</a>
            </div>

            <p class="hero__signature">
                <strong><?= e(setting('master_name')) ?></strong>
                <?= e(setting('master_role')) ?>
            </p>
        </div>

        <?php if ($master !== null): ?>
            <figure class="hero__figure">
                <?= $this->raw(\App\Core\Media::picture(
                    $master,
                    setting('master_name') . ' — ' . mb_strtolower(setting('master_role')),
                    '(min-width: 900px) 46vw, 100vw',
                    false
                )) ?>
            </figure>
        <?php endif; ?>
    </div>
</section>
