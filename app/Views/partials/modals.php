<?php
/**
 * Дві порожні оболонки, які наповнює JS: картка послуги і лайтбокс.
 * Тримати їх у розмітці дешевше, ніж створювати вузли на льоту, і це дає
 * коректний фокус-менеджмент без сюрпризів.
 */
?>
<div class="modal" data-modal="service" data-open="false" role="dialog" aria-modal="true" aria-labelledby="service-modal-title" hidden>
    <div class="modal__panel">
        <button class="modal__close" type="button" data-modal-close aria-label="Закрити">&times;</button>
        <h2 class="modal__title" id="service-modal-title"></h2>
        <p class="modal__meta" data-modal-meta></p>
        <div class="modal__body" data-modal-body></div>
        <div class="modal__actions">
            <a class="btn btn--primary" href="#zapys" data-modal-book>Записатись на процедуру</a>
            <button class="btn btn--ghost" type="button" data-modal-close>Закрити</button>
        </div>
    </div>
</div>

<div class="modal lightbox" data-modal="lightbox" data-open="false" role="dialog" aria-modal="true" aria-label="Перегляд зображення" hidden>
    <div class="modal__panel">
        <button class="modal__close" type="button" data-modal-close aria-label="Закрити">&times;</button>
        <button class="lightbox__nav lightbox__nav--prev" type="button" data-lightbox-prev aria-label="Попереднє">&#8249;</button>
        <button class="lightbox__nav lightbox__nav--next" type="button" data-lightbox-next aria-label="Наступне">&#8250;</button>
        <img alt="" data-lightbox-img>
        <p class="lightbox__caption" data-lightbox-caption></p>
    </div>
</div>
