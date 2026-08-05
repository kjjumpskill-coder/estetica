<?php
$socials = array_filter([
    'Instagram' => setting('instagram_url'),
    'Facebook'  => setting('facebook_url'),
    'TikTok'    => setting('tiktok_url'),
]);

$messengers = array_filter([
    'Telegram' => setting('telegram_url'),
    'Viber'    => setting('viber_url'),
    'WhatsApp' => setting('whatsapp_url'),
]);
?>
<footer class="footer">
    <div class="wrap">
        <div class="footer__grid">

            <div>
                <div class="footer__brand">
                    <span style="color:var(--c-nude)"><?php $this->partial('logo', ['ring' => true]) ?></span>
                    <span><?= e(setting('brand_name', 'Estetika')) ?></span>
                </div>
                <p><?= e(setting('master_full_name')) ?><br><?= e(setting('master_role')) ?></p>

                <?php if (has_setting('telegram_url')): ?>
                    <a class="btn btn--tg" href="<?= e(setting('telegram_url')) ?>?start=site"
                       target="_blank" rel="noopener" data-event="tg_subscribe">
                        Отримувати корисні поради в Telegram
                    </a>
                <?php endif; ?>
            </div>

            <div>
                <h2>Контакти</h2>
                <ul>
                    <?php if (has_setting('phone')): ?>
                        <li><a href="tel:<?= e(phone_digits(setting('phone'))) ?>" data-event="phone_click"><?= e(setting('phone')) ?></a></li>
                    <?php endif; ?>
                    <?php if (has_setting('email')): ?>
                        <li><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></li>
                    <?php endif; ?>
                    <li><?= e(setting('loc1_address')) ?></li>
                    <li><?= e(setting('loc2_address')) ?></li>
                    <li><?= e(setting('city', 'Дніпро')) ?></li>
                </ul>

                <?php if ($messengers !== [] || $socials !== []): ?>
                    <h3 style="margin-top:1.5rem">Ми в мережі</h3>
                    <ul>
                        <?php foreach ($messengers + $socials as $label => $url): ?>
                            <li>
                                <a href="<?= e($url) ?>" target="_blank" rel="noopener"
                                   data-event="messenger_click"><?= e($label) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div>
                <h2>Документи</h2>
                <ul>
                    <li><a href="/polityka-konfidenciynosti">Політика конфіденційності</a></li>
                    <li><a href="/dogovir-oferty">Договір оферти</a></li>
                    <li><a href="/pravyla-zapysu">Правила запису та відвідування</a></li>
                </ul>
            </div>
        </div>

        <div class="footer__bottom">
            <span>&copy; <?= date('Y') ?> <?= e(setting('brand_name', 'Estetika')) ?></span>
            <?php if (has_setting('legal_entity')): ?>
                <span><?= e(setting('legal_entity')) ?><?= has_setting('legal_tax_id') ? ', ' . e(setting('legal_tax_id')) : '' ?></span>
            <?php endif; ?>
        </div>
    </div>
</footer>
