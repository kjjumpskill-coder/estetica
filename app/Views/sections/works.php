<?php
/**
 * Роботи до / після — головний блок сайту.
 *
 * Матеріал буває трьох видів, і кожен рендериться по-своєму:
 *   collage — «до» і «після» вже склеєні в одну картинку (так знімають зараз),
 *             показуємо як є, з лайтбоксом;
 *   pair    — два окремі знімки, вмикається слайдер-порівняння перетягуванням;
 *   single  — тільки результат.
 *
 * @var array<int,array<string,mixed>> $works
 * @var array<int,array<string,mixed>> $filters
 */
use App\Core\Media;

// Показуємо перші 12. Кілька десятків робіт одразу розтягують мобільну сторінку
// так, що до форми запису читачка вже не доходить.
$visibleWorks = 12;
?>
<section class="section" id="roboty">
    <div class="wrap">
        <div class="section__head" data-reveal>
            <p class="eyebrow">Роботи</p>
            <h2 class="section__title">До і після</h2>
            <p class="section__lead">
                Усі фото — мої клієнтки, без ретуші форми й кольору. Свіжі роботи зняті
                одразу після процедури, тому шкіра на них ще трохи почервоніла — це нормальний
                вигляд перших годин, який минає за добу.
            </p>
        </div>

        <?php if ($filters !== []): ?>
            <ul class="filters" data-works-filters data-reveal>
                <li><button type="button" data-filter="all" aria-pressed="true">Усі роботи</button></li>
                <?php foreach ($filters as $f): ?>
                    <li>
                        <button type="button" data-filter="<?= e($f['slug']) ?>" aria-pressed="false">
                            <?= e($f['title']) ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($works === []): ?>
            <p class="works__empty">
                Галерея наповнюється. Найсвіжіші роботи поки що в Instagram.
            </p>
        <?php else: ?>
            <ul class="works__grid" data-works-grid data-lightbox-group="works">
                <?php foreach ($works as $i => $work): ?>
                    <?php
                    $after = [
                        'path_base' => $work['after_path'],
                        'width'     => $work['after_w'],
                        'height'    => $work['after_h'],
                        'lqip'      => $work['after_lqip'],
                    ];
                    $alt = $work['caption'] !== ''
                        ? $work['caption']
                        : trim(($work['service_title'] ?? 'Робота майстра') . ' — результат до і після');
                    ?>
                    <li class="work" data-service="<?= e((string) ($work['service_slug'] ?? '')) ?>"
                        <?= $i >= $visibleWorks ? 'hidden data-extra="works"' : '' ?>
                        data-reveal style="--reveal-delay: <?= min($i, 7) * 50 ?>ms">

                        <?php if ($work['layout'] === 'pair' && $work['before_path'] !== null): ?>
                            <div class="compare" data-compare>
                                <?= $this->raw(Media::picture($after, $alt, '(min-width: 1024px) 25vw, 50vw', true, 'compare__after')) ?>
                                <div class="compare__before">
                                    <?= $this->raw(Media::picture([
                                        'path_base' => $work['before_path'],
                                        'width'     => $work['before_w'],
                                        'height'    => $work['before_h'],
                                        'lqip'      => $work['before_lqip'],
                                    ], $alt . ' — до процедури', '(min-width: 1024px) 25vw, 50vw')) ?>
                                </div>
                                <span class="compare__handle" aria-hidden="true"></span>
                            </div>
                        <?php else: ?>
                            <button class="work__btn" type="button"
                                    data-lightbox="<?= e(Media::url($after, 1440)) ?>"
                                    data-caption="<?= e($alt) ?>"
                                    aria-label="Відкрити фото: <?= e($alt) ?>">
                                <?= $this->raw(Media::picture($after, $alt, '(min-width: 1024px) 25vw, 50vw')) ?>
                            </button>
                        <?php endif; ?>

                        <?php if (!empty($work['service_title'])): ?>
                            <span class="work__tag"><?= e($work['service_title']) ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php if (count($works) > $visibleWorks): ?>
                <div class="center-action">
                    <button class="btn btn--ghost" type="button" data-show-all="works">
                        Показати всі роботи (<?= count($works) ?>)
                    </button>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php $this->partial('rule') ?>
