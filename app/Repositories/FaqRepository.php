<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class FaqRepository
{
    public const TABS = [
        'faq'       => 'Часті питання',
        'contra'    => 'Протипоказання',
        'prep'      => 'Підготовка',
        'aftercare' => 'Догляд після',
    ];

    /** @return array<string,array<int,array<string,mixed>>> */
    public static function grouped(): array
    {
        $rows = Db::select(
            'SELECT tab, question, answer FROM faq WHERE is_published = 1 ORDER BY tab, sort'
        );

        $grouped = array_fill_keys(array_keys(self::TABS), []);

        foreach ($rows as $row) {
            $grouped[$row['tab']][] = $row;
        }

        return $grouped;
    }
}
