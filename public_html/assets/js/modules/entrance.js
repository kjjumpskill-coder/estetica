/**
 * Поява першого екрана і проявлення фото.
 *
 * Обидві речі тут разом, бо вони про одне: сторінка не має спалахувати
 * готовою, вона має зібратися на очах.
 */

/**
 * Запускає появу hero.
 *
 * Клас ставимо в наступному кадрі, а не одразу: якщо додати його тим самим
 * тактом, коли браузер уперше малює сторінку, переходу не буде — обидва стани
 * потраплять в один кадр, і елементи просто виникнуть.
 */
export function initHeroEntrance() {
    const hero = document.querySelector('[data-hero]');
    if (!hero) return;

    requestAnimationFrame(() => {
        requestAnimationFrame(() => hero.classList.add('is-ready'));
    });
}

/**
 * Знімає розмиття, щойно справжнє зображення завантажилось.
 *
 * Перевірка complete обов'язкова: зображення з кешу бувають готові ще до того,
 * як ми встигнемо підписатися на load, і без неї вони лишалися б розмитими.
 */
export function initImageReveal() {
    const images = document.querySelectorAll('img[data-lqip]');

    images.forEach((img) => {
        if (img.complete && img.naturalWidth > 0) {
            img.classList.add('is-loaded');

            return;
        }

        img.addEventListener('load', () => img.classList.add('is-loaded'), { once: true });

        // Якщо фото не завантажилось, лишати кадр назавжди розмитим гірше,
        // ніж показати заглушку як є.
        img.addEventListener('error', () => img.classList.add('is-loaded'), { once: true });
    });
}
