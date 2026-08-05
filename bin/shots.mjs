/**
 * Скріншоти сторінки для перевірки верстки.
 *
 *   node bin/shots.mjs [url] [outDir]
 *
 * Playwright не збирає власний Chromium під macOS 13, тому запускається
 * встановлений у системі Google Chrome (channel: 'chrome').
 *
 * Перед знімком скрипт чекає завантаження шрифтів і дає час анімаціям появи
 * відпрацювати — інакше на знімку буде порожня сторінка з opacity: 0.
 */

import { chromium } from 'playwright';
import { mkdirSync } from 'node:fs';

const url = process.argv[2] ?? 'http://localhost:8080/';
const outDir = process.argv[3] ?? './shots';

const VIEWPORTS = [
    { name: 'desktop', width: 1440, height: 900 },
    { name: 'tablet', width: 834, height: 1112 },
    { name: 'mobile', width: 390, height: 844 },
];

mkdirSync(outDir, { recursive: true });

const browser = await chromium.launch({ channel: 'chrome' });

for (const vp of VIEWPORTS) {
    const context = await browser.newContext({
        viewport: { width: vp.width, height: vp.height },
        deviceScaleFactor: 2,
        locale: 'uk-UA',
    });

    const page = await context.newPage();
    const errors = [];

    page.on('console', (msg) => msg.type() === 'error' && errors.push(msg.text()));
    page.on('pageerror', (err) => errors.push(String(err)));

    await page.goto(url, { waitUntil: 'networkidle' });
    await page.evaluate(() => document.fonts.ready);

    // Прокручуємо всю сторінку, щоб спрацювали IntersectionObserver'и,
    // потім повертаємось нагору для знімка.
    await page.evaluate(async () => {
        const step = window.innerHeight * 0.6;

        // Висоту читаємо на кожному кроці: поява блоків її змінює.
        for (let y = 0; y < document.documentElement.scrollHeight; y += step) {
            window.scrollTo(0, y);
            await new Promise((r) => setTimeout(r, 150));
        }

        window.scrollTo(0, 0);
        await new Promise((r) => setTimeout(r, 500));
    });

    // Дочекатися реальних картинок. Без цього на знімку лишаються LQIP-заглушки:
    // lazy-зображення просто не встигають завантажитись за час прокручування.
    await page.waitForFunction(
        () => [...document.images].every((img) => img.complete && img.naturalWidth > 0),
        null,
        { timeout: 30000 }
    ).catch(() => console.log('    (не всі зображення встигли завантажитись)'));

    await page.screenshot({ path: `${outDir}/${vp.name}-full.png`, fullPage: true });
    await page.screenshot({ path: `${outDir}/${vp.name}-hero.png` });

    const stats = await page.evaluate(() => ({
        hidden: document.querySelectorAll('[data-reveal]:not(.is-visible)').length,
        counter: document.querySelector('.trust__num')?.textContent,
        height: document.documentElement.scrollHeight,
    }));

    console.log(
        `${vp.name.padEnd(8)} ${vp.width}×${vp.height}  висота ${stats.height}px  ` +
        `лічильник "${stats.counter}"  нерозкритих блоків: ${stats.hidden}  ` +
        `помилок JS: ${errors.length}`
    );

    errors.forEach((e) => console.log('    ' + e));

    await context.close();
}

await browser.close();
