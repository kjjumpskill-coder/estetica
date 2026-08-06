/**
 * Форма запису: маска телефону, валідація на клієнті, відправка без перезавантаження.
 *
 * Головне правило помилок: не покладатися на статичний банер. Банер може опинитися
 * поза видимою зоною — і тоді здається, що кнопка просто нічого не робить.
 * Тому при будь-якій помилці ми ЗАВЖДИ прокручуємо до першого проблемного поля,
 * підсвічуємо його й ставимо туди фокус.
 */

const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)');
const HIGHLIGHT_MS = 3000;

/** Найближчий предок, який справді прокручується. Для полів у drawer'ах це не window. */
function scrollableParent(el) {
    let node = el.parentElement;

    while (node && node !== document.body) {
        const { overflowY } = getComputedStyle(node);
        if (/(auto|scroll|overlay)/.test(overflowY) && node.scrollHeight > node.clientHeight) {
            return node;
        }
        node = node.parentElement;
    }

    return null;
}

/** Показати помилку так, щоб її неможливо було не побачити. */
function failField(input, message) {
    const field = input.closest('.field');
    const error = field?.querySelector('.field__error');

    if (error) {
        error.textContent = message;
        error.hidden = false;
    }

    field?.classList.add('is-invalid');
    setTimeout(() => field?.classList.remove('is-invalid'), HIGHLIGHT_MS);

    const behavior = REDUCED.matches ? 'auto' : 'smooth';
    const container = scrollableParent(input);

    if (container) {
        const top = input.offsetTop - container.clientHeight / 2 + input.offsetHeight / 2;
        container.scrollTo({ top, behavior });
    } else {
        input.scrollIntoView({ behavior, block: 'center' });
    }

    // Фокус після прокручування, інакше браузер сам стрибне і зіпсує плавність.
    setTimeout(() => input.focus({ preventScroll: true }), REDUCED.matches ? 0 : 350);
}

function clearErrors(form) {
    form.querySelectorAll('.field__error').forEach((el) => {
        el.hidden = true;
        el.textContent = '';
    });
    form.querySelectorAll('.field.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
}

const MASK_PREFIX = '+38 (';

/**
 * Маска +38 (0XX) XXX-XX-XX.
 *
 * Тонкий момент: наш власний префікс «+38 (» сам складається з цифр, і при
 * повторному розборі значення вони нічим не відрізняються від набраних.
 * Тому спершу відкидаємо рівно ті два розряди, які намалювали самі, і лише
 * потім розбираємо решту. Без цього кожне натискання клавіші накопичувало
 * зайві розряди, і 0501234567 перетворювалося на +38 (005) 012-34-56.
 *
 * Поле не заповнюється префіксом наперед: підказку дає placeholder, а порожнє
 * поле лишається порожнім — інакше форма виглядає частково заповненою,
 * і в неї не можна нічого не ввести.
 */
function applyMask(input) {
    const onInput = () => {
        // Значення вже у нашому форматі — тоді перші дві цифри це намальований
        // нами префікс, а не введення користувачки.
        const wasFormatted = input.value.startsWith(MASK_PREFIX);
        let digits = input.value.replace(/\D/g, '');

        if (wasFormatted) {
            digits = digits.slice(2);
        }

        // Зводимо до абонентського номера — дев'яти цифр без коду країни й нуля.
        // Цикл, а не одна перевірка: набираючи «+380…» у полі, де «+38 (» вже
        // намальовано, людина по дорозі створює комбінації на кшталт 0380,
        // і їх треба розібрати так само спокійно, як вставлений номер.
        for (;;) {
            if (digits.startsWith('380')) { digits = digits.slice(3); continue; }
            if (digits.startsWith('0')) { digits = digits.slice(1); continue; }
            break;
        }

        digits = digits.slice(0, 9);

        if (digits === '') {
            input.value = wasFormatted && input.value !== MASK_PREFIX ? MASK_PREFIX : '';
            return;
        }

        let out = MASK_PREFIX + '0' + digits.slice(0, 2);
        if (digits.length >= 2) out += ') ' + digits.slice(2, 5);
        if (digits.length >= 5) out += '-' + digits.slice(5, 7);
        if (digits.length >= 7) out += '-' + digits.slice(7, 9);

        input.value = out;
    };

    input.addEventListener('input', onInput);
}

/**
 * Заповнює приховані поля, які не можна віддати з кешованого HTML:
 * мітки джерела переходу.
 */
function fillContext(form) {
    const params = new URLSearchParams(window.location.search);

    const set = (name, value) => {
        const field = form.elements[name];
        if (field) field.value = value ?? '';
    };

    set('page_path', window.location.pathname);
    set('referrer', document.referrer);
    set('utm_source', params.get('utm_source'));
    set('utm_medium', params.get('utm_medium'));
    set('utm_campaign', params.get('utm_campaign'));
}

export function initBookingForm() {
    const form = document.querySelector('[data-booking-form]');
    if (!form) return;

    const phone = form.querySelector('[data-phone-mask]');
    const status = form.querySelector('[data-form-status]');

    if (phone) applyMask(phone);

    fillContext(form);

    /**
     * Токен береться при першому дотику до форми, а не перед відправкою.
     *
     * Це не оптимізація, а умова роботи антиспаму: сервер відлічує час
     * заповнення від моменту видачі токена. Якби ми брали його вже на submit,
     * різниця завжди була б близькою до нуля, і перевірка втратила б сенс.
     */
    let tokenPromise = null;

    const ensureToken = () => {
        tokenPromise ??= fetch(form.dataset.tokenEndpoint, { credentials: 'same-origin' })
            .then((r) => r.json())
            .then(({ token }) => {
                form.elements._csrf.value = token;

                return token;
            })
            .catch(() => {
                // Дозволяємо спробувати ще раз при відправці.
                tokenPromise = null;

                return null;
            });

        return tokenPromise;
    };

    form.addEventListener('focusin', ensureToken, { once: true });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors(form);
        status.textContent = '';
        status.removeAttribute('data-state');

        const name = form.elements.name;
        const tel = form.elements.phone;

        if (name.value.trim().length < 2) {
            failField(name, 'Напишіть, будь ласка, як до вас звертатися');
            return;
        }

        // 380 плюс дев'ять розрядів — рівно 12 цифр.
        const digits = tel.value.replace(/\D/g, '');
        if (digits.length !== 12 || !digits.startsWith('380')) {
            failField(tel, 'Схоже, у номері не всі цифри. Формат: +38 (0XX) XXX-XX-XX');
            return;
        }

        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        status.textContent = 'Надсилаю…';

        try {
            // Зазвичай токен уже взято при першому дотику до форми; тут лише
            // дочікуємось його — або беремо, якщо той запит не вдався.
            await ensureToken();

            const response = await fetch(form.dataset.endpoint, {
                method: 'POST',
                headers: { 'X-Requested-With': 'fetch' },
                credentials: 'same-origin',
                body: new FormData(form),
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                // Сервер може вказати конкретне поле — тоді ведемо до нього так само,
                // як при клієнтській помилці.
                if (data.field && form.elements[data.field]) {
                    failField(form.elements[data.field], data.message || 'Перевірте це поле');
                } else {
                    status.textContent = data.message || 'Не вдалося надіслати. Спробуйте ще раз або зателефонуйте.';
                    status.dataset.state = 'error';
                }
                return;
            }

            form.reset();
            // Після reset приховані поля теж очистились — повертаємо контекст,
            // щоб повторна заявка не пішла без міток джерела.
            fillContext(form);

            status.textContent = data.message || 'Дякую! Я зв’яжуся з вами найближчим часом.';
            status.dataset.state = 'ok';
        } catch {
            status.textContent = 'Немає зв’язку з сервером. Спробуйте ще раз або зателефонуйте.';
            status.dataset.state = 'error';
        } finally {
            button.disabled = false;
        }
    });
}
