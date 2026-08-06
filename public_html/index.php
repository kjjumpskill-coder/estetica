<?php

declare(strict_types=1);

/**
 * Front controller. Єдина точка входу в застосунок.
 *
 * Якщо статичний кеш головної свіжий, веб-сервер віддає його ще до PHP (див. .htaccess).
 * Перевірка нижче — страховка на випадок, коли правило сервера не спрацювало.
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\BlogController;
use App\Controllers\HomeController;
use App\Controllers\LeadController;
use App\Controllers\LegalController;
use App\Core\Config;
use App\Core\Router;
use App\Core\Sitemap;
use App\Core\Staging;

// Закриває тестовий майданчик до того, як щось буде віддано назовні.
// На production не робить нічого.
Staging::protect();

$router = new Router();

$router->get('/', [new HomeController(), 'index']);

$router->get('/robots.txt', static function (): void {
    header('Content-Type: text/plain; charset=utf-8');
    echo Staging::robots(rtrim(Config::str('APP_URL', ''), '/'));
});

$lead = new LeadController();

$router->get('/api/token', [$lead, 'token']);
$router->post('/zayavka', [$lead, 'store']);

$blog = new BlogController();

$router->get('/blog', [$blog, 'index']);
$router->get('/blog/{slug}', [$blog, 'show']);

$router->get('/sitemap.xml', static function (): void {
    header('Content-Type: application/xml; charset=utf-8');

    // На тестовому майданчику мапи сайту не існує: віддавати пошуковикам перелік
    // сторінок, які ми тим самим запитом просимо не індексувати, безглуздо.
    if (Staging::isActive()) {
        http_response_code(404);

        return;
    }

    echo Sitemap::xml(rtrim(Config::str('APP_URL', ''), '/'));
});

$router->get('/polityka-konfidenciynosti', [new LegalController(), 'privacy']);
$router->get('/dogovir-oferty', [new LegalController(), 'offer']);
$router->get('/pravyla-zapysu', [new LegalController(), 'rules']);

$router->notFound(static function (): void {
    // На тестовому майданчику сюди потрапляють ще й /media та /assets:
    // Apache навмисно заводить їх у PHP, щоб вони пройшли через пароль.
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

    if (Staging::isActive() && preg_match('#^/(media|assets)/#', $path) === 1) {
        http_response_code(200);

        if (Staging::serveStatic($path)) {
            return;
        }

        http_response_code(404);
    }

    (new LegalController())->notFound();
});

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
