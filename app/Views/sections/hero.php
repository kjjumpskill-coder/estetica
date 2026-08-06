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
<?php
// Порядок появи задає --hero-step: рядки виходять один за одним, а не всі разом.
// Читачка встигає прочитати обіцянку до того, як з'являться кнопки.
?>
<section class="hero" data-hero>
    <div class="wrap hero__grid">
        <div>
            <p class="hero__brand hero__in" style="--hero-step:0">
                <?= e(setting('brand_name', 'Estetika')) ?> · <?= e(setting('city', 'Дніпро')) ?>
            </p>

            <h1 class="hero__title hero__in" style="--hero-step:1">Я вже бачу вас <em>красивими</em></h1>

            <p class="hero__lead hero__in" style="--hero-step:2"><?= e(setting('hero_subtitle')) ?></p>

            <div class="hero__actions hero__in" style="--hero-step:3">
                <a class="btn btn--primary" href="#zapys" data-event="cta_hero">Записатись</a>
                <a class="btn btn--ghost" href="#zapys" data-event="cta_consult">Безкоштовна консультація</a>
            </div>

            <p class="hero__signature hero__in" style="--hero-step:4">
                <strong><?= e(setting('master_name')) ?></strong>
                <?= e(setting('master_role')) ?>
            </p>
        </div>

        <?php if ($master !== null): ?>
            <figure class="hero__figure hero__in" style="--hero-step:1">
                <?php // Окрема обгортка потрібна, щоб повільне наближення фото
                      // обрізалось по краю кадру й не наповзало на золоту рамку. ?>
                <div class="hero__media">
                    <?= $this->raw(\App\Core\Media::picture(
                        $master,
                        setting('master_name') . ' — ' . mb_strtolower(setting('master_role')),
                        '(min-width: 900px) 46vw, 100vw',
                        false
                    )) ?>
                </div>
            </figure>
        <?php endif; ?>
    </div>
</section>
