<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class DiplomaRepository
{
    /** @return array<int,array<string,mixed>> */
    public static function published(): array
    {
        return Db::select(
            'SELECT d.id, d.title, d.year, d.issuer, d.is_award,
                    m.path_base, m.width, m.height, m.lqip, m.alt
               FROM diplomas d
               JOIN media m ON m.id = d.media_id
              WHERE d.is_published = 1
              ORDER BY d.is_award DESC, d.sort, d.year DESC'
        );
    }
}
