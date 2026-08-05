<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class ServiceRepository
{
    public const GROUPS = [
        'permanent' => 'Перманентний макіяж',
        'injection' => 'Ін’єкційна косметологія та догляд',
    ];

    /** @return array<int,array<string,mixed>> */
    public static function active(): array
    {
        return Db::select(
            'SELECT id, group_slug, slug, title, duration_text, short_desc, full_desc, icon
               FROM services
              WHERE is_active = 1
              ORDER BY group_slug, sort'
        );
    }

    /**
     * Послуги, згруповані для верстки: ['permanent' => [...], 'injection' => [...]].
     *
     * @return array<string,array<int,array<string,mixed>>>
     */
    public static function grouped(): array
    {
        $grouped = array_fill_keys(array_keys(self::GROUPS), []);

        foreach (self::active() as $service) {
            $grouped[$service['group_slug']][] = $service;
        }

        return $grouped;
    }

    /** Плаский список для випадайки у формі запису. */
    public static function forSelect(): array
    {
        return Db::select(
            'SELECT id, title, group_slug FROM services WHERE is_active = 1 ORDER BY group_slug, sort'
        );
    }
}
