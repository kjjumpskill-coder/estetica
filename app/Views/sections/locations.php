<?php
/**
 * Локації.
 *
 * Вбудованої карти тут навмисно немає — ані Google, ані OpenStreetMap.
 *
 * Google Maps ставить куки третьої сторони, а це знову тягне за собою банер згоди,
 * якого ми позбулися, відмовившись від Google Analytics. OSM-embed виглядав рішенням,
 * але на перевірці не спрацював: Leaflet усередині iframe не ініціалізується, а сам
 * фрейм віддає свій інтерфейс російською, і мову не налаштувати. На сайті, де вся
 * суть у тому, що він україномовний, це неприйнятно.
 *
 * Тому картки показують реальне фото кабінету й ведуть у Google Карти по кліку —
 * туди, де людина однаково будуватиме маршрут. Нуль сторонніх запитів, нуль куків.
 *
 * @var array<int,array<string,mixed>> $studio
 */
use App\Core\Media;

$locations = [];

foreach ([1, 2] as $n) {
    if (!has_setting("loc{$n}_address")) {
        continue;
    }

    $locations[] = [
        'title'    => setting("loc{$n}_title"),
        'address'  => setting("loc{$n}_address"),
        'district' => setting("loc{$n}_district"),
        'lat'      => (float) setting("loc{$n}_lat", '0'),
        'lng'      => (float) setting("loc{$n}_lng", '0'),
        'map_url'  => setting("loc{$n}_map_url"),
        'landmark' => setting("loc{$n}_landmark"),
        'parking'  => setting("loc{$n}_parking"),
    ];
}

if ($locations === []) {
    return;
}

$hours = trim(setting('schedule_mon_sat'));
$sunday = trim(setting('schedule_sun'));
?>
<section class="section" id="lokaciyi">
    <div class="wrap">
        <div class="section__head" data-reveal>
            <p class="eyebrow">Локації</p>
            <h2 class="section__title">Де мене знайти</h2>
            <p class="section__lead">
                Два кабінети в <?= e(setting('city_locative', 'Дніпрі')) ?>. Який саме буде зручнішим,
                домовимось під час підтвердження запису.
            </p>
        </div>

        <?php
        // Галерея кабінету стоїть окремо від адрес навмисно. Усі наявні знімки —
        // з одного кабінету, тож поставити їх у картки двох різних адрес означало б
        // показати клієнтці не те приміщення, куди вона приїде.
        // Коли з'являться фото другої локації, їх можна буде прив'язати поадресно.
        $cabinet = array_slice($studio, 1, 3);
        ?>
        <?php if ($cabinet !== []): ?>
            <ul class="studio-strip" data-lightbox-group="studio" data-reveal>
                <?php foreach ($cabinet as $photo): ?>
                    <li>
                        <button type="button"
                                data-lightbox="<?= e(Media::url($photo, 1440)) ?>"
                                data-caption="Кабінет салону <?= e(setting('brand_name')) ?>"
                                aria-label="Відкрити фото кабінету">
                            <?= $this->raw(Media::picture($photo, '', '(min-width: 700px) 30vw, 90vw')) ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="locations__grid">
            <?php foreach ($locations as $i => $loc): ?>
                <article class="location" data-reveal style="--reveal-delay: <?= $i * 80 ?>ms">

                    <div class="location__body">
                        <h3 class="location__title"><?= e($loc['title']) ?></h3>
                        <p class="location__address"><?= e($loc['address']) ?></p>
                        <?php if ($loc['district'] !== ''): ?>
                            <p class="location__district"><?= e($loc['district']) ?>, <?= e(setting('city', 'Дніпро')) ?></p>
                        <?php endif; ?>

                        <?php if ($hours !== '' || $sunday !== ''): ?>
                            <p class="location__hours">
                                <?php if ($hours !== ''): ?>Пн–Сб: <?= e($hours) ?><?php endif; ?>
                                <?php if ($hours !== '' && $sunday !== ''): ?> · <?php endif; ?>
                                <?php if ($sunday !== ''): ?>Нд: <?= e($sunday) ?><?php endif; ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($loc['landmark'] !== ''): ?>
                            <p class="location__district">Орієнтир: <?= e($loc['landmark']) ?></p>
                        <?php endif; ?>
                        <?php if ($loc['parking'] !== ''): ?>
                            <p class="location__district">Паркування: <?= e($loc['parking']) ?></p>
                        <?php endif; ?>

                        <?php if ($loc['map_url'] !== ''): ?>
                            <a class="location__link" href="<?= e($loc['map_url']) ?>"
                               target="_blank" rel="noopener" data-event="map_open">
                                Показати на карті й прокласти маршрут
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($hours === ''): ?>
            <p class="empty-note" style="margin-top:var(--sp-3)">
                Графік роботи уточнюється — поки що записуйтесь через форму, і я підтверджу зручний час.
            </p>
        <?php endif; ?>
    </div>
</section>

<?php $this->partial('rule') ?>
