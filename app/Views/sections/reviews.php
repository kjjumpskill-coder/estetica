<?php
/**
 * Відгуки.
 *
 * Вихідні відгуки — скріншоти листувань у месенджерах із повними іменами клієнток.
 * На сайті вони живуть як перенабраний текст: ім'я скорочене до першої літери
 * прізвища, мова українська. Скрін лишається доступним по кліку, якщо він є.
 *
 * @var array<int,array<string,mixed>> $reviews
 * @var int $reviewsTotal
 */
use App\Core\Media;

if ($reviews === []) {
    return;
}

$months = [1 => 'січня', 'лютого', 'березня', 'квітня', 'травня', 'червня',
           'липня', 'серпня', 'вересня', 'жовтня', 'листопада', 'грудня'];
?>
<section class="section section--surface" id="vidguky">
    <div class="wrap">
        <div class="section__head" data-reveal>
            <p class="eyebrow">Відгуки</p>
            <h2 class="section__title">Що кажуть клієнтки</h2>
        </div>

        <ul class="reviews__grid" data-reviews-grid>
            <?php foreach ($reviews as $i => $r): ?>
                <li class="review" data-reveal style="--reveal-delay: <?= min($i, 5) * 60 ?>ms">
                    <?php if (!empty($r['body'])): ?>
                        <div class="review__body"><?= $this->raw(paragraphs($r['body'])) ?></div>
                    <?php endif; ?>

                    <p class="review__meta">
                        <?php
                        // Підпис показуємо тільки коли він справді відомий: у частині
                        // скріншотів імені не видно, і вигадувати його не можна.
                        $author = trim((string) $r['author_name']);

                        if ($author !== '') {
                            echo '<span class="review__author">' . e($author) . '</span>';
                        }

                        $meta = [];

                        if (!empty($r['service_title'])) {
                            $meta[] = $r['service_title'];
                        }

                        if (!empty($r['review_date'])) {
                            $ts = strtotime((string) $r['review_date']);
                            $meta[] = (int) date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . date('Y', $ts);
                        }

                        if ($meta !== []) {
                            echo '<span>' . ($author !== '' ? '· ' : '') . e(implode(' · ', $meta)) . '</span>';
                        }
                        ?>
                    </p>

                    <?php if (!empty($r['media_path'])): ?>
                        <button class="review__source" type="button"
                                data-lightbox="<?= e(Media::url(['path_base' => $r['media_path']], 960)) ?>"
                                data-caption="Оригінал повідомлення<?= $author !== '' ? ' — ' . e($author) : '' ?>">
                            Показати оригінал повідомлення
                        </button>
                    <?php endif; ?>

                    <?php if (!empty($r['video_url'])): ?>
                        <a class="review__source" href="<?= e($r['video_url']) ?>"
                           target="_blank" rel="noopener" data-event="video_review_start">
                            Дивитись відеовідгук
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if ($reviewsTotal > count($reviews)): ?>
            <div class="center-action">
                <button class="btn btn--ghost" type="button" data-load-reviews="<?= count($reviews) ?>">
                    Показати ще
                </button>
            </div>
        <?php endif; ?>
    </div>
</section>
