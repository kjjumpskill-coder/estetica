import { openModal } from './modal.js';

/**
 * Лайтбокс для робіт, дипломів і скріншотів відгуків.
 * Гортання стрілками працює в межах однієї групи — щоб з дипломів
 * не можна було догортатися до фото робіт.
 */
export function initLightbox() {
    const modal = document.querySelector('[data-modal="lightbox"]');
    if (!modal) return;

    const img = modal.querySelector('[data-lightbox-img]');
    const caption = modal.querySelector('[data-lightbox-caption]');
    const prev = modal.querySelector('[data-lightbox-prev]');
    const next = modal.querySelector('[data-lightbox-next]');

    let group = [];
    let index = 0;

    const show = (i) => {
        if (i < 0 || i >= group.length) return;
        index = i;

        const trigger = group[i];
        img.src = trigger.dataset.lightbox;
        img.alt = trigger.dataset.caption || '';
        caption.textContent = trigger.dataset.caption || '';

        const many = group.length > 1;
        prev.hidden = !many;
        next.hidden = !many;
    };

    document.querySelectorAll('[data-lightbox]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const container = trigger.closest('[data-lightbox-group]');

            group = container
                ? [...container.querySelectorAll('[data-lightbox]')]
                : [trigger];

            show(group.indexOf(trigger));
            openModal(modal);
        });
    });

    const step = (delta) => show((index + delta + group.length) % group.length);

    prev.addEventListener('click', () => step(-1));
    next.addEventListener('click', () => step(1));

    document.addEventListener('keydown', (event) => {
        if (modal.dataset.open !== 'true') return;
        if (event.key === 'ArrowLeft') step(-1);
        if (event.key === 'ArrowRight') step(1);
    });
}
