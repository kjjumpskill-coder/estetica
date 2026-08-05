<?php
/** @var array<int,array<string,mixed>> $studio */
$portrait = $studio[0] ?? null;
?>
<section class="section" id="pro-maystra">
    <div class="wrap about__grid">

        <?php if ($portrait !== null): ?>
            <figure class="about__figure" data-reveal>
                <?php // alt беремо з самої медіа: у цьому слоті знімок кабінету, а не портрет. ?>
                <?= $this->raw(\App\Core\Media::picture(
                    $portrait,
                    '',
                    '(min-width: 900px) 38vw, 100vw'
                )) ?>
            </figure>
        <?php endif; ?>

        <div class="about__body" data-reveal style="--reveal-delay:80ms">
            <p class="eyebrow">Про майстра</p>
            <h2 class="section__title"><?= e(setting('master_full_name')) ?></h2>

            <p>
                Усе почалося у 2006 році з манікюру. Далі були навчання за навчанням —
                косметологія, перманентний макіяж, ін’єкційні методики — і кожне з них
                додавало по одному вмінню до того, що сьогодні виглядає як єдина професія.
            </p>
            <p>
                За майже двадцять років через мої руки пройшло понад п’ять тисяч жінок:
                молоді мами, підприємиці, лікарки, викладачки. Хтось приїжджає з іншого
                міста, хтось — з-за кордону, коли буває вдома. Майже всі приходять за
                чиєюсь рекомендацією, і це найточніший показник роботи, який я знаю.
            </p>
            <p>
                Зараз <?= e(setting('brand_name', 'Estetika')) ?> — це вже третій мій салон.
                Але суть роботи не змінилася з першого дня: я не переробляю обличчя.
                Я прибираю з нього втому.
            </p>

            <blockquote class="quote">
                <p><?= e(setting('master_quote')) ?></p>
                <footer><?= e(setting('master_name')) ?></footer>
            </blockquote>

            <ul class="credentials">
                <li>Бронзова та золота призерка чемпіонатів України з перманентного макіяжу</li>
                <li>Членкиня Міжнародної спілки фахівців у сфері краси</li>
                <li>Медичний коледж з відзнакою</li>
                <li>Понад 100 дипломів і сертифікатів про підвищення кваліфікації</li>
            </ul>
        </div>
    </div>
</section>

<?php $this->partial('rule') ?>
