<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class MediaRepository
{
    /**
     * Фото за категорією — портрети майстра, інтер'єр салону тощо.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function byCategory(string $category, int $limit = 24): array
    {
        return Db::select(
            'SELECT id, path_base, width, height, lqip, alt
               FROM media
              WHERE category = ?
              ORDER BY id
              LIMIT ?',
            [$category, $limit]
        );
    }

    /** @return array<string,mixed>|null */
    public static function first(string $category): ?array
    {
        return Db::selectOne(
            'SELECT id, path_base, width, height, lqip, alt
               FROM media WHERE category = ? ORDER BY id LIMIT 1',
            [$category]
        );
    }
}
