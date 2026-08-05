import { openModal, closeModal } from './modal.js';

/**
 * Картка послуги → модальне вікно з розгорнутим описом.
 * Дані беруться з data-атрибутів картки, тому додатковий запит не потрібен.
 */
export function initServiceModal() {
    const modal = document.querySelector('[data-modal="service"]');
    if (!modal) return;

    const title = modal.querySelector('#service-modal-title');
    const meta = modal.querySelector('[data-modal-meta]');
    const body = modal.querySelector('[data-modal-body]');
    const book = modal.querySelector('[data-modal-book]');

    document.querySelectorAll('[data-service]').forEach((card) => {
        card.addEventListener('click', () => {
            title.textContent = card.dataset.title || '';

            meta.textContent = card.dataset.duration || '';
            meta.hidden = !card.dataset.duration;

            body.innerHTML = '';
            (card.dataset.desc || '')
                .split(/\n\s*\n/)
                .filter(Boolean)
                .forEach((chunk) => {
                    const p = document.createElement('p');
                    // textContent, а не innerHTML: опис приходить з бази й редагується
                    // в адмінці, тому не повинен уміти виконувати розмітку.
                    p.textContent = chunk.trim();
                    body.appendChild(p);
                });

            openModal(modal);
        });
    });

    // Кнопка «Записатись» усередині модалки має і закрити її, і докрутити до форми.
    if (book) {
        book.addEventListener('click', () => closeModal(modal));
    }
}
