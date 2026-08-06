<?php
/**
 * Форма запису — головна конверсійна дія.
 *
 * Сітки вільних годин тут немає навмисно. Замовниця веде записи особисто, без CRM,
 * тому сайт не може забронювати слот. Якщо намалювати календар із «вільними» годинами,
 * клієнтки приходитимуть на зайняті — тому форма чесно називає себе заявкою,
 * а бажаний час лишається побажанням у вільній формі.
 *
 * @var array<int,array<string,mixed>> $formServices
 */
use App\Core\Csrf;
use App\Repositories\ServiceRepository;

$grouped = [];
foreach ($formServices as $s) {
    $grouped[$s['group_slug']][] = $s;
}
?>
<section class="section" id="zapys">
    <div class="wrap booking">

        <div data-reveal>
            <p class="eyebrow">Запис</p>
            <h2 class="section__title">Залишіть заявку</h2>
            <p class="section__lead">
                Напишіть, що вас цікавить, і я зв’яжуся з вами, щоб узгодити час.
                Якщо ще не визначились із процедурою — теж пишіть, розберемось на консультації.
            </p>

            <div class="booking__aside">
                <?php if (has_setting('phone')): ?>
                    <ul class="contact-list">
                        <li>
                            <a href="tel:<?= e(phone_digits(setting('phone'))) ?>" data-event="phone_click">
                                <?= e(setting('phone')) ?>
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>

                <?php
                $messengers = array_filter([
                    'Telegram' => setting('telegram_url'),
                    'Viber'    => setting('viber_url'),
                    'WhatsApp' => setting('whatsapp_url'),
                ]);
                ?>
                <?php if ($messengers !== []): ?>
                    <div class="hero__actions">
                        <?php foreach ($messengers as $label => $url): ?>
                            <a class="btn btn--ghost" href="<?= e($url) ?>" target="_blank"
                               rel="noopener" data-event="messenger_click"><?= e($label) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php
                // Що буде далі — головне, чого не вистачає людині перед відправкою форми.
                // Цей блок ще й тримає ліву колонку заповненою, поки контакти не вказані.
                ?>
                <ol class="steps steps--compact">
                    <li class="step">
                        <h3>Я передзвоню або напишу</h3>
                        <p>У той спосіб, який ви оберете. Зазвичай того ж дня.</p>
                    </li>
                    <li class="step">
                        <h3>Узгодимо час</h3>
                        <p>Підберемо зручні день і кабінет. Заявка стає записом саме тут.</p>
                    </li>
                    <li class="step">
                        <h3>Розкажу, як підготуватись</h3>
                        <p>Коротко й конкретно під вашу процедуру.</p>
                    </li>
                </ol>
            </div>
        </div>

        <form class="form" data-booking-form data-endpoint="/zayavka" data-token-endpoint="/api/token"
              method="post" action="/zayavka" novalidate data-reveal>

            <?php
            // CSRF-токена немає в самій розмітці свідомо. Головна сторінка на проді
            // віддається зі статичного кешу — один і той самий HTML для всіх, — тому
            // вкладений сюди токен був би спільним і застарілим уже за годину.
            // JS бере свіжий токен із /api/token безпосередньо перед відправкою.
            ?>
            <input type="hidden" name="<?= e(Csrf::fieldName()) ?>" value="">

            <?php // Три шари антиспаму без CAPTCHA: пастка, час заповнення і ліміт на IP. ?>
            <div class="hp" aria-hidden="true">
                <label for="website">Не заповнюйте це поле</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <?php // Мітки джерела проставляє JS: у кешованому HTML вони застигли б
                  // на моменті генерації сторінки. Часу заповнення тут немає навмисно —
                  // його відлічує сервер від моменту видачі токена. ?>
            <input type="hidden" name="page_path" value="">
            <input type="hidden" name="referrer" value="">
            <input type="hidden" name="utm_source" value="">
            <input type="hidden" name="utm_medium" value="">
            <input type="hidden" name="utm_campaign" value="">

            <div class="field">
                <label for="lead-name">Як до вас звертатися <span class="req" aria-hidden="true">*</span></label>
                <input type="text" id="lead-name" name="name" required autocomplete="given-name"
                       maxlength="120" placeholder="Марія">
                <p class="field__error" data-error-for="name" hidden></p>
            </div>

            <div class="field">
                <label for="lead-phone">Телефон <span class="req" aria-hidden="true">*</span></label>
                <input type="tel" id="lead-phone" name="phone" required autocomplete="tel"
                       inputmode="tel" placeholder="+38 (0__) ___-__-__" data-phone-mask>
                <p class="field__error" data-error-for="phone" hidden></p>
            </div>

            <div class="field">
                <label for="lead-service">Що вас цікавить</label>
                <select id="lead-service" name="service_id">
                    <option value="">Ще не визначилась — потрібна консультація</option>
                    <?php foreach ($grouped as $groupSlug => $items): ?>
                        <optgroup label="<?= e(ServiceRepository::GROUPS[$groupSlug] ?? '') ?>">
                            <?php foreach ($items as $s): ?>
                                <option value="<?= (int) $s['id'] ?>"><?= e($s['title']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="lead-channel">Як зручніше зв’язатись</label>
                    <select id="lead-channel" name="contact_channel">
                        <option value="phone">Телефон</option>
                        <option value="telegram">Telegram</option>
                        <option value="viber">Viber</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>
                </div>

                <div class="field">
                    <label for="lead-date">Коли вам зручно</label>
                    <input type="text" id="lead-date" name="preferred_date" maxlength="120"
                           placeholder="Напр.: будні після 15:00">
                </div>
            </div>

            <div class="field">
                <label for="lead-comment">Коментар</label>
                <textarea id="lead-comment" name="comment" maxlength="2000"
                          placeholder="Що для вас важливо, чого побоюєтесь, чи робили процедуру раніше"></textarea>
            </div>

            <div>
                <button class="btn btn--primary" type="submit" data-event="form_submit">Надіслати заявку</button>
                <p class="form__note" style="margin-top:var(--sp-2)">
                    Це заявка, а не готовий запис. Я зв’яжуся з вами найближчим часом,
                    щоб підтвердити зручний час.
                </p>
                <p class="form__status" data-form-status role="status" aria-live="polite"></p>
            </div>
        </form>
    </div>
</section>
