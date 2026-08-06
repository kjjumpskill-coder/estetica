<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\PageCache;
use App\Core\View;
use App\Repositories\DiplomaRepository;
use App\Repositories\FaqRepository;
use App\Repositories\MediaRepository;
use App\Repositories\PostRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\ServiceRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\WorkRepository;

final class HomeController
{
    public function index(): void
    {
        // Кеш перевіряється тут як страховка: основне правило віддачі статики
        // живе в .htaccess і спрацьовує до старту PHP.
        if (PageCache::isFresh()) {
            $cached = PageCache::read();

            if ($cached !== null) {
                header('Content-Type: text/html; charset=utf-8');
                header('X-Cache: hit');
                echo $cached;

                return;
            }
        }

        $view = new View();

        $html = $view->page('pages/home', [
            'settings'  => SettingsRepository::all(),
            'services'  => ServiceRepository::grouped(),
            'works'     => WorkRepository::published(),
            'filters'   => WorkRepository::filters(),
            'diplomas'  => DiplomaRepository::published(),
            'reviews'   => ReviewRepository::published(9),
            'reviewsTotal' => ReviewRepository::countPublished(),
            'faq'       => FaqRepository::grouped(),
            'studio'    => MediaRepository::byCategory('studio', 8),
            'master'    => MediaRepository::first('master'),
            'formServices' => ServiceRepository::forSelect(),
            'posts'     => PostRepository::published(3),
            'pageTitle' => null,
        ]);

        PageCache::write($html);

        header('Content-Type: text/html; charset=utf-8');
        header('X-Cache: miss');
        echo $html;
    }
}
