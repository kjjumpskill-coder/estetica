<?php
/**
 * Інтерв'ю на телебаченні.
 *
 * Стоїть одразу після історії майстра, бо це підтвердження ззовні: не «я про себе»,
 * а «мене запросили в ефір». Такий доказ важить більше за будь-який перелік регалій.
 *
 * Технічно це фасад: показуємо превʼю з YouTube і створюємо iframe лише після кліку.
 * Інакше плеєр тягне понад пів мегабайта скриптів ще до того, як хтось натисне play,
 * і псує показники першого екрана.
 */
$videoId = setting('interview_youtube_id');

if ($videoId === '') {
    return;
}
?>
<section class="section" id="interviu">
    <div class="wrap">
        <div class="section__head section__head--center" data-reveal>
            <p class="eyebrow">Інтерв’ю</p>
            <h2 class="section__title">Ольга в ефірі «<?= e(setting('interview_title', 'Новий день')) ?>»</h2>
            <p class="section__lead">
                Розмова про професію, про те, як змінювалася індустрія за двадцять років,
                і чому природність складніша за яскравий результат.
            </p>
        </div>

        <button class="interview__frame" type="button"
                data-youtube="<?= e($videoId) ?>"
                data-event="interview_play"
                aria-label="Відтворити інтерв’ю з Ольгою Кіріловою">
            <?php // Превʼю лежить локально: інакше сторінка робить запит на сервери Google
                  // ще до того, як хтось натиснув play — тобто рівно те, чого ми уникаємо. ?>
            <img src="/assets/img/interview-cover.jpg"
                 alt="Кадр з інтерв’ю: <?= e(setting('master_name')) ?> в телестудії"
                 width="1280" height="720" loading="lazy" decoding="async">
            <span class="interview__play">
                <svg viewBox="0 0 22 26" aria-hidden="true"><path d="M0 0l22 13L0 26z"/></svg>
            </span>
        </button>

        <p class="interview__caption"><?= e(setting('interview_note')) ?></p>
    </div>
</section>

<?php $this->partial('rule') ?>
