/**
 * Блок робіт: фільтр по послугах і слайдер-порівняння.
 *
 * Слайдер існує тільки для робіт з layout='pair' — там, де в базі є два окремі
 * знімки. Наявні зараз фото це склеєні колажі, у яких «до» і «після» вже в одній
 * картинці, тому на них слайдера немає й бути не може.
 */

export function initWorksFilter() {
    const filters = document.querySelector('[data-works-filters]');
    const grid = document.querySelector('[data-works-grid]');
    if (!filters || !grid) return;

    filters.addEventListener('click', (event) => {
        const button = event.target.closest('[data-filter]');
        if (!button) return;

        const value = button.dataset.filter;

        filters.querySelectorAll('[data-filter]').forEach((b) => {
            b.setAttribute('aria-pressed', String(b === button));
        });

        grid.querySelectorAll('[data-service]').forEach((item) => {
            item.hidden = value !== 'all' && item.dataset.service !== value;
        });

        // Фільтр шукає по всій галереї, а не лише по видимій дюжині — інакше
        // «Ботулінотерапія» показувала б не всі роботи, а тільки ті, що встигли
        // потрапити на перший показ. Але це означає, що ліміт уже знято,
        // тому кнопка «показати всі» тут більше не має сенсу.
        document.querySelector('[data-show-all="works"]')?.remove();
    });
}

export function initCompare() {
    document.querySelectorAll('[data-compare]').forEach((node) => {
        let dragging = false;

        const setFromClientX = (clientX) => {
            const rect = node.getBoundingClientRect();
            const ratio = (clientX - rect.left) / rect.width;
            const clamped = Math.max(0, Math.min(1, ratio));

            // Без інерції й згладжування: розділювач має йти рівно за пальцем.
            node.style.setProperty('--pos', `${(clamped * 100).toFixed(2)}%`);
        };

        const start = (event) => {
            dragging = true;
            setFromClientX(event.clientX ?? event.touches[0].clientX);
        };

        const move = (event) => {
            if (!dragging) return;
            const x = event.clientX ?? (event.touches && event.touches[0].clientX);
            if (x !== undefined) setFromClientX(x);
        };

        const end = () => { dragging = false; };

        node.addEventListener('pointerdown', start);
        node.addEventListener('pointermove', move);
        window.addEventListener('pointerup', end);
        node.addEventListener('pointerleave', end);
    });
}

/**
 * Кнопки «Показати всі». Кожна розкриває тільки свою групу — інакше кнопка
 * під дипломами відкривала б заодно й приховані роботи.
 */
export function initShowAll() {
    document.querySelectorAll('[data-show-all]').forEach((button) => {
        button.addEventListener('click', () => {
            const group = button.dataset.showAll;

            document
                .querySelectorAll(`[data-extra="${group}"]`)
                .forEach((el, i) => {
                    el.hidden = false;

                    // Показуємо одразу, не чекаючи прокручування. Ці картки
                    // відкриті свідомим натисканням, і лишати їх на opacity: 0
                    // до наступного руху сторінки означало б показати порожнечу
                    // у відповідь на клік.
                    el.style.setProperty('--reveal-delay', `${Math.min(i, 8) * 40}ms`);
                    requestAnimationFrame(() => el.classList.add('is-visible'));
                });

            button.remove();
        });
    });
}
