/**
 * Поява елементів при прокручуванні + промальовування ліній-розділювачів.
 *
 * Жодна з цих анімацій не змінює розмір елемента — рухається тільки transform
 * і opacity. Інакше поява штовхала б сусідній контент і псувала CLS.
 */

const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)');

export function initReveal() {
    const targets = document.querySelectorAll('[data-reveal]');
    const rules = document.querySelectorAll('.rule[data-draw]');

    // Довжина шляху потрібна для stroke-dasharray. Рахуємо її з самої геометрії,
    // а не вписуємо константою: лінія розтягується на всю ширину контейнера.
    rules.forEach((svg) => {
        const path = svg.querySelector('path');
        if (path) {
            svg.style.setProperty('--len', Math.ceil(path.getTotalLength()));
        }
    });

    if (REDUCED.matches) {
        targets.forEach((el) => el.classList.add('is-visible'));
        rules.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        },
        // Від'ємного нижнього rootMargin тут свідомо немає. Він робить появу
        // трохи ефектнішою, але створює мертву зону: елемент, який опинився
        // в нижніх відсотках екрана вже на самому низу сторінки, не проявиться
        // ніколи — гортати далі нікуди, і подія не настане.
        { rootMargin: '0px', threshold: 0.05 }
    );

    targets.forEach((el) => observer.observe(el));
    rules.forEach((el) => observer.observe(el));

    installSafetyNet(observer);
}

/**
 * Страховка від «непроявлених» блоків.
 *
 * IntersectionObserver повідомляє про перетин лише тоді, коли воно справді сталося
 * в якомусь із кадрів. Якщо сторінку перекинуло різко — переходом по якорю в меню,
 * відновленням позиції прокручування або переверсткою колонкової галереї після
 * завантаження фото — блок може «проскочити» повз екран, жодного разу в ньому
 * не опинившись. Тоді він назавжди лишається на opacity: 0, і на сторінці дірка.
 *
 * Тому: усе, що вже вище видимої зони, показуємо беззастережно. Слухач знімає
 * себе сам, щойно непроявлених блоків не лишиться.
 */
function installSafetyNet(observer) {
    let pending = [...document.querySelectorAll('[data-reveal]')];

    const sweep = () => {
        pending = pending.filter((el) => {
            if (el.classList.contains('is-visible')) {
                return false;
            }

            // Прокрутили повз — анімувати вже нема сенсу, просто показуємо.
            if (el.getBoundingClientRect().bottom < 0) {
                el.classList.add('is-visible');
                observer.unobserve(el);

                return false;
            }

            return true;
        });

        if (pending.length === 0) {
            window.removeEventListener('scroll', onScroll);
        }
    };

    let scheduled = false;
    const onScroll = () => {
        if (scheduled) return;
        scheduled = true;

        requestAnimationFrame(() => {
            scheduled = false;
            sweep();
        });
    };

    window.addEventListener('scroll', onScroll, { passive: true });
}
