<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class ReviewRepository
{
    /** @return array<int,array<string,mixed>> */
    public static function published(int $limit = 9, int $offset = 0): array
    {
        return Db::select(
            'SELECT r.id, r.type, r.author_name, r.body, r.video_url, r.review_date,
                    s.title AS service_title,
                    m.path_base AS media_path, m.width AS media_w, m.height AS media_h, m.lqip AS media_lqip
               FROM reviews r
          LEFT JOIN media m ON m.id = r.media_id
          LEFT JOIN services s ON s.id = r.service_id
              WHERE r.is_published = 1
              ORDER BY r.sort, r.review_date DESC, r.id DESC
              LIMIT ? OFFSET ?',
            [$limit, $offset]
        );
    }

    public static function countPublished(): int
    {
        return (int) Db::scalar('SELECT COUNT(*) FROM reviews WHERE is_published = 1');
    }
}
