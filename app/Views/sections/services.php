<?php
/**
 * Послуги.
 *
 * Ціни не показуються свідомо: вартість залежить від препарату й курсу валют,
 * а прайс, який застаріває за місяць, гірший за його відсутність. Замість ціни —
 * кнопка запиту вартості.
 *
 * @var array<string,array<int,array<string,mixed>>> $services
 */
use App\Repositories\ServiceRepository;
?>
<section class="section section--surface" id="poslugy">
    <div class="wrap">
        <div class="section__head" data-reveal>
            <p class="eyebrow">Послуги</p>
            <h2 class="section__title">Що я роблю</h2>
            <p class="section__lead">
                Натисніть на картку, щоб прочитати, як проходить процедура і чого від неї чекати.
                Вартість називаю особисто — вона залежить від препарату, а ціни на препарати
                прив’язані до курсу.
            </p>
        </div>

        <?php foreach ($services as $groupSlug => $items): ?>
            <?php if ($items === []) { continue; } ?>

            <div class="services__group">
                <h3 class="services__group-title" data-reveal>
                    <?= e(ServiceRepository::GROUPS[$groupSlug] ?? '') ?>
                </h3>

                <ul class="cards">
                    <?php foreach ($items as $i => $service): ?>
                        <li data-reveal style="--reveal-delay: <?= min($i, 5) * 60 ?>ms">
                            <button class="card" type="button"
                                    data-service="<?= e((string) $service['id']) ?>"
                                    data-title="<?= e($service['title']) ?>"
                                    data-duration="<?= e($service['duration_text']) ?>"
                                    data-desc="<?= e((string) $service['full_desc']) ?>">
                                <span class="card__icon"><?php $this->partial('icon', ['name' => $service['icon']]) ?></span>
                                <span class="card__title"><?= e($service['title']) ?></span>
                                <?php if ($service['duration_text'] !== ''): ?>
                                    <span class="card__meta"><?= e($service['duration_text']) ?></span>
                                <?php endif; ?>
                                <span class="card__desc"><?= e($service['short_desc']) ?></span>
                                <span class="card__more">Докладніше</span>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

        <div class="center-action">
            <a class="btn btn--primary" href="#zapys" data-event="cta_services">Дізнатись вартість</a>
        </div>
    </div>
</section>
