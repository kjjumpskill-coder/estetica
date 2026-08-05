/**
 * Перевірка інтерактиву на живій сторінці.
 *
 *   node bin/smoke.mjs [url]
 *
 * Перевіряє те, що не видно на скріншоті: чи відкриваються модалки, чи перемикаються
 * вкладки, чи веде валідація форми до проблемного поля. Виходить з кодом 1,
 * якщо хоч одна перевірка впала.
 */

import { chromium } from 'playwright';

const url = process.argv[2] ?? 'http://localhost:8080/';

const browser = await chromium.launch({ channel: 'chrome' });
const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });

const jsErrors = [];
page.on('pageerror', (e) => jsErrors.push(String(e)));
page.on('console', (m) => m.type() === 'error' && jsErrors.push(m.text()));

const results = [];
const check = async (name, fn) => {
    try {
        const detail = await fn();
        results.push({ name, ok: true, detail });
    } catch (e) {
        results.push({ name, ok: false, detail: String(e).split('\n')[0] });
    }
};

await page.goto(url, { waitUntil: 'networkidle' });

await check('модалка послуги відкривається і містить опис', async () => {
    await page.locator('[data-service]').first().click();
    const modal = page.locator('[data-modal="service"]');
    await modal.waitFor({ state: 'visible', timeout: 3000 });

    const title = await modal.locator('.modal__title').innerText();
    const paras = await modal.locator('[data-modal-body] p').count();

    if (!title || paras === 0) throw new Error('модалка порожня');

    await modal.locator('[data-modal-close]').first().click();
    await page.waitForTimeout(400);

    return `«${title.slice(0, 30)}…», абзаців: ${paras}`;
});

await check('Escape закриває модалку', async () => {
    await page.locator('[data-service]').first().click();
    await page.waitForTimeout(300);
    await page.keyboard.press('Escape');
    await page.waitForTimeout(400);

    const open = await page.locator('[data-modal="service"][data-open="true"]').count();
    if (open !== 0) throw new Error('лишилася відкритою');

    return 'закрилася';
});

// Порядок важливий: «показати всі» перевіряється до фільтра, бо перший же клік
// по фільтру знімає ліміт показу і прибирає цю кнопку.
await check('«показати всі роботи» розкриває приховані', async () => {
    const before = await page.locator('[data-works-grid] .work:visible').count();
    await page.locator('[data-show-all="works"]').click();
    await page.waitForTimeout(500);
    const after = await page.locator('[data-works-grid] .work:visible').count();

    if (after <= before) throw new Error(`не додалось: ${before} → ${after}`);

    // Розкриті картки мають ще й проявитись, а не лишитись на opacity: 0.
    // Гортаємо галерею від самого верху: клік по кнопці внизу переносить нас
    // одразу за неї, і частина карток лишається позаду непереглянутою.
    await page.evaluate(async () => {
        const grid = document.querySelector('[data-works-grid]');
        const rect = grid.getBoundingClientRect();
        const top = rect.top + window.scrollY;
        const bottom = rect.bottom + window.scrollY;

        for (let y = top - window.innerHeight; y < bottom + window.innerHeight; y += window.innerHeight * 0.5) {
            window.scrollTo(0, Math.max(0, y));
            await new Promise((r) => setTimeout(r, 160));
        }
        await new Promise((r) => setTimeout(r, 800));
    });

    const stuck = await page.locator('[data-works-grid] .work:visible[data-reveal]:not(.is-visible)').count();
    if (stuck > 0) throw new Error(`${stuck} карток лишились на opacity: 0`);

    return `${before} → ${after}, усі проявились`;
});

await check('фільтр робіт ховає зайве', async () => {
    const before = await page.locator('[data-works-grid] .work:visible').count();
    await page.locator('[data-filter]:not([data-filter="all"])').first().click();
    await page.waitForTimeout(300);
    const after = await page.locator('[data-works-grid] .work:visible').count();

    if (after === 0) throw new Error('після фільтра не лишилось нічого');
    if (after >= before) throw new Error(`не відфільтрувалось: було ${before}, стало ${after}`);

    await page.locator('[data-filter="all"]').click();
    await page.waitForTimeout(300);

    return `${before} → ${after}`;
});

await check('вкладки FAQ перемикаються', async () => {
    const tabs = page.locator('[data-tab]');
    const n = await tabs.count();
    if (n < 2) throw new Error('вкладок менше двох');

    await tabs.nth(1).click();
    await page.waitForTimeout(200);

    const key = await tabs.nth(1).getAttribute('data-tab');
    const visible = await page.locator(`[data-panel="${key}"]`).isVisible();
    if (!visible) throw new Error('панель не показалась');

    return `${n} вкладки, активна «${key}»`;
});

await check('лайтбокс відкривається на дипломі', async () => {
    await page.locator('#dyplomy [data-lightbox]').first().click();
    const box = page.locator('[data-modal="lightbox"]');
    await box.waitFor({ state: 'visible', timeout: 3000 });

    const src = await box.locator('[data-lightbox-img]').getAttribute('src');
    if (!src || !src.includes('.webp')) throw new Error('немає зображення');

    await page.keyboard.press('Escape');
    await page.waitForTimeout(400);

    return src.split('/').pop();
});

await check('маска телефону не втрачає й не додає цифр', async () => {
    const input = page.locator('[data-phone-mask]');

    // Три способи набору того самого номера мають дати однаковий результат.
    const cases = [
        ['0501234567', '+38 (050) 123-45-67'],
        ['380501234567', '+38 (050) 123-45-67'],
        ['501234567', '+38 (050) 123-45-67'],
    ];

    for (const [typed, expected] of cases) {
        await input.fill('');
        await input.click();
        await input.type(typed, { delay: 5 });

        const value = await input.inputValue();
        if (value !== expected) {
            throw new Error(`набрано "${typed}" → отримано "${value}", очікували "${expected}"`);
        }
    }

    await input.fill('');
    return `${cases.length} варіанти набору дають +38 (050) 123-45-67`;
});

await check('порожня форма веде до першого проблемного поля', async () => {
    // Відкручуємо вгору, щоб поле точно було поза екраном на момент відправки.
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.waitForTimeout(300);

    await page.evaluate(() => {
        document.querySelector('[data-booking-form]')
            .dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
    });

    await page.waitForTimeout(1200);

    const state = await page.evaluate(() => {
        const el = document.getElementById('lead-name');
        const r = el.getBoundingClientRect();

        return {
            focused: document.activeElement === el,
            highlighted: el.closest('.field').classList.contains('is-invalid'),
            error: el.closest('.field').querySelector('.field__error')?.textContent ?? '',
            inView: r.top >= 0 && r.bottom <= window.innerHeight,
        };
    });

    if (!state.inView) throw new Error('поле не потрапило у видиму зону');
    if (!state.highlighted) throw new Error('поле не підсвічене');
    if (!state.focused) throw new Error('фокус не поставлено');
    if (!state.error) throw new Error('немає тексту помилки');

    return `«${state.error}», у фокусі й підсвічене`;
});

await check('неповний телефон теж перехоплюється', async () => {
    await page.fill('#lead-name', 'Тест');
    await page.fill('#lead-phone', '+38 (050) 12');

    await page.evaluate(() => {
        document.querySelector('[data-booking-form]')
            .dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
    });

    await page.waitForTimeout(1000);

    const state = await page.evaluate(() => {
        const el = document.getElementById('lead-phone');
        const r = el.getBoundingClientRect();
        return {
            highlighted: el.closest('.field').classList.contains('is-invalid'),
            error: el.closest('.field').querySelector('.field__error')?.textContent ?? '',
            inView: r.top >= 0 && r.bottom <= window.innerHeight,
        };
    });

    if (!state.highlighted || !state.inView) throw new Error('поле не підсвічене або поза екраном');

    return `«${state.error.slice(0, 40)}…»`;
});

await check('фасад відео не тягне YouTube до кліку', async () => {
    const before = await page.locator('#interviu iframe').count();
    if (before !== 0) throw new Error('iframe є ще до кліку');

    await page.locator('[data-youtube]').click();
    await page.waitForTimeout(600);

    const after = await page.locator('#interviu iframe').count();
    if (after !== 1) throw new Error('iframe не створився після кліку');

    return 'до кліку 0, після кліку 1';
});

console.log();
let failed = 0;

for (const r of results) {
    console.log(`  ${r.ok ? 'ok  ' : 'ПАДІННЯ'} ${r.name}\n        ${r.detail}`);
    if (!r.ok) failed++;
}

console.log(`\n  Помилок JS на сторінці: ${jsErrors.length}`);
jsErrors.slice(0, 5).forEach((e) => console.log('    ' + e.slice(0, 140)));

console.log(`\n  Пройдено ${results.length - failed} з ${results.length}`);

await browser.close();
process.exit(failed > 0 || jsErrors.length > 0 ? 1 : 0);
