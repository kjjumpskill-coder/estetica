/**
 * Спільна механіка модальних вікон: відкриття, закриття, замикання фокуса,
 * блокування прокручування тіла сторінки.
 *
 * Використовується і карткою послуги, і лайтбоксом — щоб поведінка клавіатури
 * була однаковою в обох.
 */

const FOCUSABLE = 'a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])';

let lastFocused = null;

export function openModal(el) {
    lastFocused = document.activeElement;

    el.hidden = false;
    // Наступний кадр — щоб перехід opacity справді програвся, а не застосувався одразу.
    requestAnimationFrame(() => el.dataset.open = 'true');

    document.body.classList.add('is-locked');

    const first = el.querySelector(FOCUSABLE);
    if (first) first.focus();
}

export function closeModal(el) {
    el.dataset.open = 'false';
    document.body.classList.remove('is-locked');

    const done = () => {
        el.hidden = true;
        el.removeEventListener('transitionend', done);
    };
    el.addEventListener('transitionend', done);
    // Підстраховка, якщо перехід не спрацював (наприклад, prefers-reduced-motion).
    setTimeout(done, 320);

    if (lastFocused instanceof HTMLElement) lastFocused.focus();
}

export function initModals() {
    document.querySelectorAll('[data-modal]').forEach((modal) => {
        modal.addEventListener('click', (event) => {
            // Клік по підкладці закриває; клік усередині панелі — ні.
            if (event.target === modal || event.target.closest('[data-modal-close]')) {
                closeModal(modal);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        const open = document.querySelector('[data-modal][data-open="true"]');
        if (!open) return;

        if (event.key === 'Escape') {
            closeModal(open);
            return;
        }

        if (event.key !== 'Tab') return;

        const items = [...open.querySelectorAll(FOCUSABLE)].filter((el) => el.offsetParent !== null);
        if (items.length === 0) return;

        const first = items[0];
        const last = items[items.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });
}
