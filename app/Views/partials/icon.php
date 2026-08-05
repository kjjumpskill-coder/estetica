<?php
/**
 * Іконки послуг.
 *
 * Навмисно абстрактні: дуга брови, вигин губи, лінія вій, крапля. Літеральні
 * малюнки процедур на такому сайті виглядають як кліпарт, тому всі знаки зведені
 * до одного каркаса — сітка 24×24, однакова товщина лінії, лише currentColor.
 *
 * @var string $name
 */
$paths = [
    // Перманентний макіяж
    'brows'       => '<path d="M3 14c3.5-5 8.5-7 13-6.5M16 7.5c2 .3 3.7 1.2 5 2.5"/>',
    'lips'        => '<path d="M3 12c3-4 6-4 9-1 3-3 6-3 9 1-3 5-6 6.5-9 6.5S6 17 3 12z"/>',
    'interlash'   => '<path d="M3 14c4-4.5 14-4.5 18 0"/><path d="M6 12.6V9M10 11.2V7.4M14 11.2V7.4M18 12.6V9"/>',
    'eyeliner'    => '<path d="M3 14c4-4.5 12-4.5 16 0"/><path d="M19 14c1.4.3 2.3-.6 2-2"/>',
    'correction'  => '<path d="M4 17.5 15.5 6a2.1 2.1 0 0 1 3 3L7 20.5l-4 1z"/>',
    'removal'     => '<path d="M4 12h16"/><path d="M7.5 8.5 4 12l3.5 3.5"/><circle cx="17" cy="12" r="3.2"/>',

    // Ін'єкційна косметологія
    'botulinum'         => '<path d="M4 18c3.5-1.5 5.5-4 6.5-7"/><path d="M8 20c4-2 6.5-5.5 8-10"/><path d="M13 21c4.5-2.5 6.5-6.5 7-11"/>',
    'lips-filler'       => '<path d="M4 12.5c2.7-3.4 5.3-3.4 8-.8 2.7-2.6 5.3-2.6 8 .8-2.7 4.6-5.3 6-8 6s-5.3-1.4-8-6z"/><path d="M12 4v3.5M10.4 5.8h3.2"/>',
    'biorevitalization' => '<path d="M12 3.5C15.5 8 18 10.8 18 14a6 6 0 0 1-12 0c0-3.2 2.5-6 6-10.5z"/>',
    'mesotherapy'       => '<circle cx="7" cy="8" r="1.3"/><circle cx="13" cy="6" r="1.3"/><circle cx="17.5" cy="10" r="1.3"/><circle cx="9" cy="14.5" r="1.3"/><circle cx="15" cy="16.5" r="1.3"/><circle cx="5.5" cy="18" r="1.3"/>',
    'polynucleotides'   => '<path d="M8 3c0 5 8 5 8 9s-8 4-8 9"/><path d="M16 3c0 5-8 5-8 9s8 4 8 9"/>',
    'lipolytics'        => '<path d="M5 8c3.5 3 10.5 3 14 0"/><path d="M6.5 13c3 2 8 2 11 0"/><path d="M9 17.5c2 1 4 1 6 0"/>',
    'lifting'           => '<path d="M4 16c4-1.5 5.5-4.5 6-8"/><path d="M20 16c-4-1.5-5.5-4.5-6-8"/><path d="M12 21V9"/><path d="M9 11.5 12 8.5l3 3"/>',
    'consultation'      => '<path d="M4 6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5v7a2.5 2.5 0 0 1-2.5 2.5H10l-5 4v-4H6.5"/>',
];

$path = $paths[$name] ?? $paths['consultation'];
?>
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.15"
     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    <?= $this->raw($path) ?>
</svg>
