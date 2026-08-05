<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;
use App\Repositories\SettingsRepository;

/**
 * Юридичні сторінки й 404.
 *
 * Тексти документів — заглушки з явною поміткою. Вигадувати остаточні юридичні
 * формулювання за замовницю не можна: вона підписує їх своїм ФОП.
 */
final class LegalController
{
    private const PAGES = [
        'privacy' => ['slug' => 'polityka-konfidenciynosti', 'title' => 'Політика конфіденційності'],
        'offer'   => ['slug' => 'dogovir-oferty',            'title' => 'Договір оферти'],
        'rules'   => ['slug' => 'pravyla-zapysu',            'title' => 'Правила запису та відвідування'],
    ];

    public function privacy(): void
    {
        $this->render('privacy');
    }

    public function offer(): void
    {
        $this->render('offer');
    }

    public function rules(): void
    {
        $this->render('rules');
    }

    public function notFound(): void
    {
        http_response_code(404);
        header('Content-Type: text/html; charset=utf-8');

        echo (new View())->page('pages/not-found', [
            'settings'  => SettingsRepository::all(),
            'pageTitle' => 'Сторінку не знайдено',
            'noindex'   => true,
        ]);
    }

    private function render(string $key): void
    {
        $page = self::PAGES[$key];

        header('Content-Type: text/html; charset=utf-8');

        echo (new View())->page('pages/legal/' . $key, [
            'settings'  => SettingsRepository::all(),
            'pageTitle' => $page['title'],
            'pageSlug'  => $page['slug'],
            'noindex'   => true,
        ]);
    }
}
