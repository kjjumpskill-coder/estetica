<?php

declare(strict_types=1);

/**
 * Спільний старт для веб-запитів і CLI-скриптів із bin/.
 */

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';
require BASE_PATH . '/app/helpers.php';

use App\Core\Config;
use App\Core\ErrorHandler;

Config::load(BASE_PATH);
ErrorHandler::register();

date_default_timezone_set('Europe/Kyiv');
setlocale(LC_TIME, 'uk_UA.UTF-8', 'uk_UA', 'ukrainian');
