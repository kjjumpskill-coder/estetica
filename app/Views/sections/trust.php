<?php
/**
 * Довіра одним рядком. Лічильники анімуються при появі в екрані;
 * значення беруться з налаштувань, а не зашиті у верстку.
 */
$items = [
    ['value' => setting('counter_years', '20'),   'suffix' => '+', 'label' => 'років у сфері краси'],
    ['value' => setting('counter_clients', '5000'), 'suffix' => '+', 'label' => 'задоволених клієнток'],
    ['value' => setting('counter_awards', '2'),   'suffix' => '',  'label' => 'призові місця на чемпіонатах України'],
    ['value' => setting('counter_certs', '100'),  'suffix' => '+', 'label' => 'дипломів і сертифікатів'],
];
?>
<section class="trust">
    <div class="wrap">
        <ul class="trust__list">
            <?php foreach ($items as $i => $item): ?>
                <li data-reveal style="--reveal-delay: <?= $i * 70 ?>ms">
                    <span class="trust__num"
                          data-counter="<?= e($item['value']) ?>"
                          data-suffix="<?= e($item['suffix']) ?>">0<?= e($item['suffix']) ?></span>
                    <span class="trust__label"><?= e($item['label']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
