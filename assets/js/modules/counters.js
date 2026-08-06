/**
 * Анімований підрахунок цифр довіри.
 *
 * Ширина комірки не змінюється під час рахунку — за це відповідає
 * font-variant-numeric: tabular-nums у CSS. Без нього рядок смикається.
 */

const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)');
const DURATION = 1600;

const easeOut = (t) => 1 - Math.pow(1 - t, 3);

function run(el) {
    const target = parseInt(el.dataset.counter, 10);
    const suffix = el.dataset.suffix || '';

    if (Number.isNaN(target)) return;

    if (REDUCED.matches) {
        el.textContent = target.toLocaleString('uk-UA') + suffix;
        return;
    }

    const start = performance.now();

    const tick = (now) => {
        const progress = Math.min((now - start) / DURATION, 1);
        const value = Math.round(target * easeOut(progress));

        el.textContent = value.toLocaleString('uk-UA') + suffix;

        if (progress < 1) requestAnimationFrame(tick);
    };

    requestAnimationFrame(tick);
}

export function initCounters() {
    const counters = document.querySelectorAll('[data-counter]');
    if (counters.length === 0) return;

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                run(entry.target);
                observer.unobserve(entry.target);
            });
        },
        { threshold: 0.5 }
    );

    counters.forEach((el) => observer.observe(el));
}
