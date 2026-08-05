/**
 * Дрібна поведінка інтерфейсу: таби FAQ, мобільне меню, стан шапки,
 * плаваюча кнопка запису й фасад відео.
 */

export function initTabs() {
    const tabs = document.querySelector('.tabs');
    if (!tabs) return;

    tabs.addEventListener('click', (event) => {
        const button = event.target.closest('[data-tab]');
        if (!button) return;

        const key = button.dataset.tab;

        tabs.querySelectorAll('[data-tab]').forEach((b) => {
            b.setAttribute('aria-selected', String(b === button));
        });

        document.querySelectorAll('[data-panel]').forEach((panel) => {
            panel.hidden = panel.dataset.panel !== key;
        });
    });

    // Стрілками між вкладками — так очікує клавіатурна навігація по role="tablist".
    tabs.addEventListener('keydown', (event) => {
        if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;

        const buttons = [...tabs.querySelectorAll('[data-tab]')];
        const current = buttons.indexOf(document.activeElement);
        if (current === -1) return;

        const step = event.key === 'ArrowRight' ? 1 : -1;
        const target = buttons[(current + step + buttons.length) % buttons.length];

        target.focus();
        target.click();
    });
}

export function initMobileNav() {
    const panel = document.querySelector('[data-nav-panel]');
    const openBtn = document.querySelector('[data-nav-open]');
    if (!panel || !openBtn) return;

    const setOpen = (open) => {
        panel.dataset.open = String(open);
        openBtn.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('is-locked', open);
    };

    openBtn.addEventListener('click', () => setOpen(true));

    panel.querySelectorAll('[data-nav-close], [data-nav-link]').forEach((el) => {
        el.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && panel.dataset.open === 'true') setOpen(false);
    });
}

export function initHeaderState() {
    const header = document.querySelector('[data-header]');
    const floatCta = document.querySelector('[data-float-cta]');
    const form = document.getElementById('zapys');

    const onScroll = () => {
        if (header) {
            header.dataset.scrolled = String(window.scrollY > 12);
        }

        if (floatCta) {
            // Ховаємо плаваючу кнопку, коли форма вже на екрані — інакше вона
            // перекриває саме те поле, до якого веде.
            const formVisible = form
                ? form.getBoundingClientRect().top < window.innerHeight
                : false;

            floatCta.classList.toggle('is-visible', window.scrollY > 600 && !formVisible);
        }
    };

    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}

/**
 * Фасад YouTube. Плеєр важить понад пів мегабайта скриптів, тому до кліку
 * на сторінці лежить лише картинка.
 */
export function initVideoFacade() {
    document.querySelectorAll('[data-youtube]').forEach((facade) => {
        facade.addEventListener('click', () => {
            const iframe = document.createElement('iframe');

            iframe.src = `https://www.youtube-nocookie.com/embed/${facade.dataset.youtube}?autoplay=1&rel=0`;
            iframe.title = 'Інтерв’ю з Ольгою Кіріловою';
            iframe.allow = 'accelerometer; autoplay; encrypted-media; picture-in-picture';
            iframe.allowFullscreen = true;

            facade.innerHTML = '';
            facade.appendChild(iframe);
        }, { once: true });
    });
}
