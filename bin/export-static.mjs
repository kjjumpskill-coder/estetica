/**
 * Статичний зліпок сайту для GitHub Pages.
 *
 *   node bin/export-static.mjs [baseUrl] [outDir] [prefix]
 *
 * Це попередній перегляд для узгодження, а не сайт. PHP тут не виконується,
 * тому форма заявки не працює — замість неї в копію підставляється чесне
 * пояснення, а не кнопка, що веде в нікуди.
 *
 * Скріншоти відгуків у копію не потрапляють свідомо. Фото робіт замовниця
 * публікує в інстаграмі сама, а листування у Viber і Facebook — ні: це
 * приватне спілкування з клієнтками, і воно не стає публічним заодно.
 */

import { mkdir, writeFile, cp, rm } from 'node:fs/promises';
import { dirname } from 'node:path';

const base = process.argv[2] ?? 'http://localhost:8080';
const outDir = process.argv[3] ?? './build';
const prefix = process.argv[4] ?? '/estetica';

// Сторінка → куди покласти. Вкладені index.html, щоб працювали «чисті» адреси.
const PAGES = [
    ['/', 'index.html'],
    ['/blog', 'blog/index.html'],
    ['/blog/chomu-kolir-znykaye-na-pyatyy-den', 'blog/chomu-kolir-znykaye-na-pyatyy-den/index.html'],
    ['/blog/yak-pidgotuvatys-do-procedury', 'blog/yak-pidgotuvatys-do-procedury/index.html'],
    ['/blog/perekryttya-roboty-inshogo-maystra', 'blog/perekryttya-roboty-inshogo-maystra/index.html'],
    ['/polityka-konfidenciynosti', 'polityka-konfidenciynosti/index.html'],
    ['/dogovir-oferty', 'dogovir-oferty/index.html'],
    ['/pravyla-zapysu', 'pravyla-zapysu/index.html'],
    ['/storinku-ne-znaydeno', '404.html'],
];

/** Замість робочої форми — пояснення, чому її тут немає. */
const FORM_NOTICE = `<div class="static-notice">
    <p class="eyebrow">Попередній перегляд</p>
    <h3>Тут форма запису не працює</h3>
    <p>Ця сторінка — статична копія для узгодження вигляду. На робочому сайті
    кнопка надсилає заявку майстру. Поки що напишіть, будь ласка, в Instagram.</p>
    <a class="btn btn--primary" href="https://www.instagram.com/kirillova_ok_permanent_studio/"
       target="_blank" rel="noopener">Написати в Instagram</a>
</div>`;

const NOTICE_CSS = `
<style>
.static-notice {
    padding: var(--sp-4);
    background: var(--c-surface);
    border: 1px solid var(--c-line);
    border-radius: var(--radius);
    display: grid;
    gap: var(--sp-2);
    justify-items: start;
}
.static-notice h3 { font-family: var(--font-display); font-size: var(--t-lg); font-weight: 500; }
.static-notice p { color: var(--c-muted); max-width: 46ch; }
.static-banner {
    position: sticky; top: 0; z-index: 500;
    padding: 0.55rem var(--sp-3);
    background: var(--c-ink); color: var(--c-bg);
    font-size: var(--t-xs); text-align: center;
}
.static-banner a { color: var(--c-nude); text-decoration: underline; }
</style>`;

const BANNER = `<div class="static-banner">Попередній перегляд для узгодження ·
Форма запису й Telegram працюють лише на робочому сайті ·
<a href="https://github.com/kjjumpskill-coder/estetica">Про проєкт</a></div>`;

function rewrite(html) {
    let out = html;

    // Порядок важливий. Спершу посилання на сторінки — поки шляхи ще без префікса
    // і ресурси можна відрізнити від сторінок за початком шляху. Якщо зробити
    // навпаки, href до css уже виглядатиме як «/estetica/assets/...», перевірка
    // його не впізнає, і він отримає префікс удруге та ще й слеш на кінці.
    out = out.replace(/href="\/(?!\/)([^"]*)"/g, (_m, path) => {
        // Ресурси лишаємо як є — їх підхопить заміна нижче.
        if (path.startsWith('assets/') || path.startsWith('media/')) {
            return `href="/${path}"`;
        }

        if (path === '') return `href="${prefix}/"`;
        if (path.startsWith('#')) return `href="${prefix}/${path}"`;

        return `href="${prefix}/${path.replace(/\/$/, '')}/"`;
    });

    // Тепер абсолютні шляхи до ресурсів — і в href, і в src, і в srcset, і в CSS.
    out = out.replaceAll('/assets/', `${prefix}/assets/`);
    out = out.replaceAll('/media/', `${prefix}/media/`);

    // Форма замінюється поясненням: клікати по кнопці, яка нічого не робить, гірше,
    // ніж чесно сказати, що це копія.
    out = out.replace(/<form class="form"[\s\S]*?<\/form>/, FORM_NOTICE);

    // Кнопки «показати оригінал повідомлення» ведуть на скріншоти листувань,
    // яких у копії немає.
    out = out.replace(/<button class="review__source"[\s\S]*?<\/button>/g, '');

    // Банер і стилі до нього.
    out = out.replace('<a class="skip-link"', `${BANNER}\n<a class="skip-link"`);
    out = out.replace('</head>', `${NOTICE_CSS}\n</head>`);

    return out;
}

await rm(outDir, { recursive: true, force: true });
await mkdir(outDir, { recursive: true });

for (const [path, target] of PAGES) {
    const response = await fetch(base + path);
    const html = rewrite(await response.text());

    const file = `${outDir}/${target}`;
    await mkdir(dirname(file), { recursive: true });
    await writeFile(file, html, 'utf8');

    console.log(`  ${String(response.status).padEnd(4)} ${path} → ${target}`);
}

// Ресурси. media/reviews не копіюється — див. коментар угорі файла.
await cp('public_html/assets', `${outDir}/assets`, { recursive: true });

for (const dir of ['works', 'diplomas', 'studio', 'master']) {
    await cp(`public_html/media/${dir}`, `${outDir}/media/${dir}`, { recursive: true });
}

// Без цього GitHub Pages проганяє все через Jekyll і ігнорує теки з підкресленням.
await writeFile(`${outDir}/.nojekyll`, '', 'utf8');

console.log('\n  Ресурси скопійовано. Скріншоти відгуків пропущено навмисно.');
