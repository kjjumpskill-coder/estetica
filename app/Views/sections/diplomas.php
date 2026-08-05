<?php
/**
 * Дипломи, сертифікати, нагороди.
 *
 * Показуємо перші 12, решту — по кнопці. Понад сотня документів одразу
 * перетворює блок довіри на стіну, яку ніхто не гортає.
 *
 * @var array<int,array<string,mixed>> $diplomas
 */
use App\Core\Media;

if ($diplomas === []) {
    return;
}

$visible = 12;
?>
<section class="section" id="dyplomy">
    <div class="wrap">
        <div class="section__head" data-reveal>
            <p class="eyebrow">Кваліфікація</p>
            <h2 class="section__title">Дипломи, сертифікати, нагороди</h2>
            <p class="section__lead">
                Навчання не закінчується — методики й препарати змінюються швидше,
                ніж встигає застаріти диплом.
            </p>
        </div>

        <ul class="diplomas__grid" data-lightbox-group="diplomas">
            <?php foreach ($diplomas as $i => $d): ?>
                <li>
                    <figure class="diploma" data-award="<?= (int) $d['is_award'] ?>"
                            <?= $i >= $visible ? 'hidden data-extra="diplomas"' : '' ?>
                            data-reveal style="--reveal-delay: <?= min($i, 7) * 45 ?>ms">
                        <button type="button"
                                data-lightbox="<?= e(Media::url($d, 1440)) ?>"
                                data-caption="<?= e(trim($d['title'] . ' ' . ($d['year'] ? '· ' . $d['year'] : ''))) ?>"
                                data-event="diploma_open"
                                aria-label="Відкрити: <?= e($d['title']) ?>">
                            <?= $this->raw(Media::picture($d, $d['title'], '(min-width: 768px) 22vw, 45vw')) ?>
                        </button>
                        <?php if ($d['title'] !== '' || $d['year']): ?>
                            <figcaption>
                                <?= e($d['title']) ?><?= $d['year'] ? ' · ' . e((string) $d['year']) : '' ?>
                            </figcaption>
                        <?php endif; ?>
                    </figure>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (count($diplomas) > $visible): ?>
            <div class="center-action">
                <button class="btn btn--ghost" type="button" data-show-all="diplomas">
                    Показати всі (<?= count($diplomas) ?>)
                </button>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php $this->partial('rule') ?>
