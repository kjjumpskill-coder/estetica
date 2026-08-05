<?php

declare(strict_types=1);

/**
 * Front controller. Єдина точка входу в застосунок.
 *
 * Якщо статичний кеш головної свіжий, веб-сервер віддає його ще до PHP (див. .htaccess).
 * Перевірка нижче — страховка на випадок, коли правило сервера не спрацювало.
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\HomeController;
use App\Controllers\LegalController;
use App\Core\Router;

$router = new Router();

$router->get('/', [new HomeController(), 'index']);

$router->get('/polityka-konfidenciynosti', [new LegalController(), 'privacy']);
$router->get('/dogovir-oferty', [new LegalController(), 'offer']);
$router->get('/pravyla-zapysu', [new LegalController(), 'rules']);

$router->notFound(static function (): void {
    (new LegalController())->notFound();
});

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
