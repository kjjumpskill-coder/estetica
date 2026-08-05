<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class WorkRepository
{
    /**
     * Опубліковані роботи з приєднаною медіа.
     *
     * Поле layout визначає, як блок рендериться:
     *   collage — одне зображення, у якому «до» і «після» вже склеєні
     *   single  — фото результату без «до»
     *   pair    — дві окремі картинки, вмикається слайдер-порівняння
     *
     * @return array<int,array<string,mixed>>
     */
    public static function published(?int $serviceId = null): array
    {
        $sql = 'SELECT w.id, w.layout, w.caption, w.service_id,
                       s.title AS service_title, s.slug AS service_slug,
                       a.path_base AS after_path, a.width AS after_w, a.height AS after_h,
                       a.lqip AS after_lqip, a.alt AS after_alt,
                       b.path_base AS before_path, b.width AS before_w, b.height AS before_h,
                       b.lqip AS before_lqip, b.alt AS before_alt
                  FROM works w
                  JOIN media a ON a.id = w.after_media_id
             LEFT JOIN media b ON b.id = w.before_media_id
             LEFT JOIN services s ON s.id = w.service_id
                 WHERE w.is_published = 1';

        $params = [];

        if ($serviceId !== null) {
            $sql .= ' AND w.service_id = ?';
            $params[] = $serviceId;
        }

        $sql .= ' ORDER BY w.sort, w.id DESC';

        return Db::select($sql, $params);
    }

    /**
     * Послуги, у яких є хоч одна опублікована робота — з цього будуються кнопки фільтра.
     * Показувати фільтр по послузі, під якою нічого немає, немає сенсу.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function filters(): array
    {
        return Db::select(
            'SELECT s.id, s.slug, s.title, COUNT(w.id) AS n
               FROM services s
               JOIN works w ON w.service_id = s.id AND w.is_published = 1
              GROUP BY s.id, s.slug, s.title
              ORDER BY s.group_slug, s.sort'
        );
    }
}
