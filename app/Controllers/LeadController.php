<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Db;
use App\Core\Logger;
use App\Core\RateLimit;
use App\Core\Session;
use Throwable;

/**
 * Приймання заявок із форми на сайті.
 *
 * Головне правило розділу: втрата заявки неприпустима. Тому запис у базу —
 * перша дія після валідації, і жодна подальша помилка (сповіщення, аналітика)
 * не може її скасувати. Надсилання в Telegram додається на наступному етапі
 * і теж не матиме права ламати збереження.
 */
final class LeadController
{
    /** Мінімальний час заповнення. Швидше за це форму заповнюють тільки боти. */
    private const MIN_FILL_SECONDS = 3;

    private const SESSION_OPENED_AT = '_form_opened_at';

    /**
     * Токен для форми.
     *
     * Окремий запит потрібен через статичний кеш головної: HTML віддається
     * з диска однаковий для всіх, тому вкладений у нього токен був би спільним
     * і застарілим. JS бере токен звідси при першій взаємодії з формою.
     *
     * Заразом запам'ятовуємо момент видачі. Саме він, а не присланий клієнтом
     * час, слугує відліком для перевірки швидкості заповнення.
     */
    public function token(): void
    {
        Session::start();

        // Перезаписуємо тільки якщо відлік ще не почато: інакше повторний
        // запит токена скидав би секундомір і знецінював перевірку.
        if (Session::get(self::SESSION_OPENED_AT) === null) {
            Session::set(self::SESSION_OPENED_AT, time());
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        echo json_encode(['token' => Csrf::token()], JSON_UNESCAPED_UNICODE);
    }

    public function store(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        try {
            $this->handle();
        } catch (Throwable $e) {
            Logger::error('Помилка обробки заявки: ' . $e->getMessage(), [
                'file' => $e->getFile() . ':' . $e->getLine(),
            ]);

            $this->fail('Сталася технічна помилка. Спробуйте ще раз або напишіть у месенджер.', null, 500);
        }
    }

    private function handle(): void
    {
        if (!Csrf::check($_POST[Csrf::fieldName()] ?? null)) {
            // Найчастіша побутова причина — сторінка провисіла відкритою добу
            // й сесія встигла закінчитись. Формулювання має підказувати дію,
            // а не звинувачувати у зломі.
            $this->fail('Сторінка була відкрита надто довго. Оновіть її, будь ласка, і надішліть ще раз.');
        }

        // Шар 1: пастка. Поле приховане css'ом, людина його не бачить.
        if (trim((string) ($_POST['website'] ?? '')) !== '') {
            Logger::info('Заявку відхилено: заповнена пастка');
            // Боту показуємо успіх — інакше він одразу підбере обхід.
            $this->ok();
        }

        // Шар 2: час заповнення.
        //
        // Відлік ведеться від моменту, коли сервер видав токен, і зберігається
        // в сесії. Присланий клієнтом started_at для цього не годиться взагалі:
        // це годинник чужого браузера, який може бути зсунутий на хвилини,
        // а бот просто підставить туди потрібне число.
        $openedAt = (int) Session::get(self::SESSION_OPENED_AT, 0);

        if ($openedAt === 0) {
            // Токен не брали через /api/token — значить, форму не відкривали.
            Logger::info('Заявку відхилено: відлік часу не починався');
            $this->ok();
        }

        $elapsed = time() - $openedAt;

        if ($elapsed < self::MIN_FILL_SECONDS) {
            Logger::info("Заявку відхилено: форму заповнено за {$elapsed} с");
            // Боту показуємо успіх — інакше він одразу підбере обхід.
            $this->ok();
        }

        // Шар 3: частота з однієї адреси.
        $ipHash = RateLimit::ipHash();

        if (RateLimit::exceeded($ipHash)) {
            $this->fail(
                'З вашої адреси вже надійшло кілька заявок. Я з вами зв’яжуся — '
                . 'а якщо терміново, напишіть у месенджер.',
                null,
                429
            );
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));

        if (mb_strlen($name) < 2) {
            $this->fail('Напишіть, будь ласка, як до вас звертатися', 'name');
        }

        if (mb_strlen($name) > 120) {
            $this->fail('Занадто довге ім’я', 'name');
        }

        $digits = preg_replace('/\D/', '', $phone) ?? '';

        // Не довіряємо клієнтській масці: на сервері перевіряємо самі цифри.
        // Український номер — це 380 плюс дев'ять розрядів, тобто рівно 12.
        if (strlen($digits) !== 12 || !str_starts_with($digits, '380')) {
            $this->fail('Схоже, у номері не всі цифри. Формат: +38 (0XX) XXX-XX-XX', 'phone');
        }

        $serviceId = (int) ($_POST['service_id'] ?? 0);

        if ($serviceId > 0) {
            $exists = Db::scalar('SELECT id FROM services WHERE id = ? AND is_active = 1', [$serviceId]);
            $serviceId = $exists === null ? 0 : $serviceId;
        }

        $channels = ['phone', 'telegram', 'viber', 'whatsapp'];
        $channel = (string) ($_POST['contact_channel'] ?? 'phone');
        $channel = in_array($channel, $channels, true) ? $channel : 'phone';

        $id = Db::insert(
            'INSERT INTO leads
                (name, phone, contact_channel, service_id, preferred_date, comment,
                 page_path, utm_source, utm_medium, utm_campaign, referrer, source, ip_hash)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $name,
                '+' . $digits,
                $channel,
                $serviceId > 0 ? $serviceId : null,
                $this->clip($_POST['preferred_date'] ?? '', 120),
                $this->clip($_POST['comment'] ?? '', 2000),
                $this->clip($_POST['page_path'] ?? '/', 255),
                $this->clip($_POST['utm_source'] ?? '', 120),
                $this->clip($_POST['utm_medium'] ?? '', 120),
                $this->clip($_POST['utm_campaign'] ?? '', 120),
                $this->clip($_POST['referrer'] ?? '', 255),
                'site',
                $ipHash,
            ]
        );

        // Секундомір скидаємо: наступна заявка з цієї ж сесії має відлічуватись
        // наново, а не зараховувати час, витрачений на попередню.
        Session::forget(self::SESSION_OPENED_AT);

        Logger::info('Нова заявка №' . $id);

        // Тут на четвертому етапі стане надсилання в Telegram. Воно піде після
        // збереження і в try/catch: недоступний бот не має коштувати заявки.

        $this->ok('Дякую! Я зв’яжуся з вами найближчим часом, щоб підтвердити зручний час.');
    }

    private function clip(mixed $value, int $max): string
    {
        return mb_substr(trim((string) $value), 0, $max);
    }

    private function ok(string $message = 'Дякую! Заявку прийнято.'): never
    {
        echo json_encode(['ok' => true, 'message' => $message], JSON_UNESCAPED_UNICODE);

        exit;
    }

    private function fail(string $message, ?string $field = null, int $status = 422): never
    {
        http_response_code($status);

        echo json_encode(
            array_filter(['ok' => false, 'message' => $message, 'field' => $field]),
            JSON_UNESCAPED_UNICODE
        );

        exit;
    }
}
