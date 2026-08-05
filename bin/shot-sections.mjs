/**
 * Знімки окремих секцій — щоб дивитися на верстку блоками, а не однією
 * стрічкою на чотирнадцять тисяч пікселів.
 *
 *   node bin/shot-sections.mjs [url] [outDir] [width]
 */

import { chromium } from 'playwright';
import { mkdirSync } from 'node:fs';

const url = process.argv[2] ?? 'http://localhost:8080/';
const outDir = process.argv[3] ?? './shots/sections';
const width = Number(process.argv[4] ?? 1440);

const SECTIONS = [
    'pro-maystra', 'interviu', 'poslugy', 'roboty', 'yak-prohodyt',
    'bezpeka', 'dyplomy', 'vidguky', 'pytannya', 'sertyfikaty',
    'lokaciyi', 'zapys',
];

mkdirSync(outDir, { recursive: true });

const browser = await chromium.launch({ channel: 'chrome' });
const context = await browser.newContext({
    viewport: { width, height: 900 },
    deviceScaleFactor: 1.5,
    locale: 'uk-UA',
});

const page = await context.newPage();
await page.goto(url, { waitUntil: 'networkidle' });
await page.evaluate(() => document.fonts.ready);

await page.evaluate(async () => {
    const step = window.innerHeight * 0.6;
    for (let y = 0; y < document.documentElement.scrollHeight; y += step) {
        window.scrollTo(0, y);
        await new Promise((r) => setTimeout(r, 150));
    }
});

// Дочекатися реальних картинок, інакше в кадр потраплять LQIP-заглушки.
await page.waitForFunction(
    () => [...document.images].every((img) => img.complete && img.naturalWidth > 0),
    null,
    { timeout: 30000 }
).catch(() => console.log('  (не всі зображення встигли завантажитись)'));

// Карти — це lazy-iframe'и зі стороннього сервера, їм потрібно більше часу,
// ніж власним зображенням.
await page.waitForTimeout(4000);

for (const id of SECTIONS) {
    const el = page.locator(`#${id}`);

    if (await el.count() === 0) {
        console.log(`  немає секції: ${id}`);
        continue;
    }

    await el.screenshot({ path: `${outDir}/${id}.png` });
    console.log(`  ok: ${id}`);
}

await browser.close();
