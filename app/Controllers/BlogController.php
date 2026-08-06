<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Media;
use App\Core\View;
use App\Repositories\PostRepository;
use App\Repositories\SettingsRepository;

final class BlogController
{
    public function index(): void
    {
        header('Content-Type: text/html; charset=utf-8');

        echo (new View())->page('pages/blog/index', [
            'settings'  => SettingsRepository::all(),
            'posts'     => PostRepository::published(30),
            'pageTitle' => 'Блог',
            'description' => 'Про догляд, підготовку до процедур і те, що відбувається '
                . 'зі шкірою після них. Пише Ольга Кірілова, косметолог-естетист із Дніпра.',
        ]);
    }

    public function show(array $params): void
    {
        $post = PostRepository::bySlug((string) ($params['slug'] ?? ''));

        if ($post === null) {
            (new LegalController())->notFound();

            return;
        }

        header('Content-Type: text/html; charset=utf-8');

        echo (new View())->page('pages/blog/show', [
            'settings'    => SettingsRepository::all(),
            'post'        => $post,
            'others'      => PostRepository::others($post['slug']),
            'pageTitle'   => $post['title'],
            'description' => $post['excerpt'],
            'ogImage'     => $post['path_base'] !== null ? Media::url($post, 1440) : null,
        ]);
    }
}
