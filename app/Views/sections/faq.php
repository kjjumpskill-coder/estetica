<?php
/**
 * Питання і відповіді. Чотири вкладки, у кожній акордеон.
 *
 * @var array<string,array<int,array<string,mixed>>> $faq
 */
use App\Repositories\FaqRepository;

$tabs = array_filter(
    FaqRepository::TABS,
    static fn(string $key): bool => !empty($faq[$key]),
    ARRAY_FILTER_USE_KEY
);

if ($tabs === []) {
    return;
}

$first = array_key_first($tabs);
?>
<section class="section" id="pytannya">
    <div class="wrap">
        <div class="section__head" data-reveal>
            <p class="eyebrow">Питання і відповіді</p>
            <h2 class="section__title">Те, що питають найчастіше</h2>
        </div>

        <div data-reveal>
            <div class="tabs" role="tablist" aria-label="Розділи питань">
                <?php foreach ($tabs as $key => $label): ?>
                    <button type="button" role="tab"
                            id="tab-<?= e($key) ?>"
                            aria-controls="panel-<?= e($key) ?>"
                            aria-selected="<?= $key === $first ? 'true' : 'false' ?>"
                            data-tab="<?= e($key) ?>"><?= e($label) ?></button>
                <?php endforeach; ?>
            </div>

            <?php foreach ($tabs as $key => $label): ?>
                <div class="accordion" role="tabpanel"
                     id="panel-<?= e($key) ?>"
                     aria-labelledby="tab-<?= e($key) ?>"
                     data-panel="<?= e($key) ?>"
                     <?= $key === $first ? '' : 'hidden' ?>>
                    <?php foreach ($faq[$key] as $item): ?>
                        <details>
                            <summary><?= e($item['question']) ?></summary>
                            <div class="accordion__answer"><?= $this->raw(paragraphs($item['answer'])) ?></div>
                        </details>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
