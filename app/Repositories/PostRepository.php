<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class PostRepository
{
    /** @return array<int,array<string,mixed>> */
    public static function published(int $limit = 3): array
    {
        return Db::select(
            'SELECT p.id, p.slug, p.title, p.excerpt, p.published_at,
                    m.path_base, m.width, m.height, m.lqip
               FROM posts p
          LEFT JOIN media m ON m.id = p.cover_media_id
              WHERE p.is_published = 1 AND p.published_at <= NOW()
              ORDER BY p.published_at DESC
              LIMIT ?',
            [$limit]
        );
    }

    /** @return array<string,mixed>|null */
    public static function bySlug(string $slug): ?array
    {
        return Db::selectOne(
            'SELECT p.id, p.slug, p.title, p.excerpt, p.body, p.published_at,
                    m.path_base, m.width, m.height, m.lqip
               FROM posts p
          LEFT JOIN media m ON m.id = p.cover_media_id
              WHERE p.slug = ? AND p.is_published = 1 AND p.published_at <= NOW()',
            [$slug]
        );
    }

    /** Дві сусідні статті — щоб зі сторінки статті було куди піти далі. */
    public static function others(string $exceptSlug, int $limit = 2): array
    {
        return Db::select(
            'SELECT slug, title, excerpt FROM posts
              WHERE is_published = 1 AND published_at <= NOW() AND slug <> ?
              ORDER BY published_at DESC LIMIT ?',
            [$exceptSlug, $limit]
        );
    }
}
