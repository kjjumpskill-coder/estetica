<?php
/**
 * Логотип Estetika.
 *
 * Вихідний знак — червоні глянцеві губи на білому колі. Форма впізнавана й лишається,
 * але заливка й колір суперечили б палітрі («жодних яскравих агресивних кольорів»),
 * тому знак перемальовано контуром у currentColor. У шапці він золотий, у футері —
 * молочний. Оригінал лежить у public_html/assets/img/logo-original.jpg.
 *
 * @var bool $ring чи малювати обвідне коло
 */
$ring = $ring ?? false;
?>
<svg viewBox="0 0 48 48" fill="none" aria-hidden="true" focusable="false"
     stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round">
    <?php if ($ring): ?>
        <circle cx="24" cy="24" r="22.4" stroke-width="0.75" opacity=".5"/>
    <?php endif; ?>
    <path d="M4.5 23.6C8.2 17.6 13 13.6 17.2 16.6c2.6 1.9 4.6 3.6 6.8 3.6s4.2-1.7 6.8-3.6c4.2-3 9 1 12.7 7"/>
    <path d="M4.5 23.6c5.6 8.9 12.4 13 19.5 13s13.9-4.1 19.5-13"/>
    <path d="M4.5 23.6c6.3 1.9 12.8 2.8 19.5 2.8s13.2-.9 19.5-2.8" stroke-width="1"/>
</svg>
