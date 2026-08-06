<?php

declare(strict_types=1);

namespace App\Core;

use App\Repositories\PostRepository;

/**
 * Мапа сайту. Збирається на льоту з реальних сторінок, а не лежить статичним
 * файлом — інакше кожна нова стаття вимагала б ручного правлення.
 *
 * Юридичні сторінки сюди не потрапляють: вони віддаються з noindex, і подавати
 * їх у мапу означало б суперечити самим собі.
 */
final class Sitemap
{
    public static function xml(string $baseUrl): string
    {
        $urls = [
            ['loc' => $baseUrl . '/', 'priority' => '1.0', 'freq' => 'weekly'],
            ['loc' => $baseUrl . '/blog', 'priority' => '0.6', 'freq' => 'monthly'],
        ];

        foreach (PostRepository::published(500) as $post) {
            $urls[] = [
                'loc'      => $baseUrl . '/blog/' . $post['slug'],
                'priority' => '0.5',
                'freq'     => 'yearly',
                'lastmod'  => date('Y-m-d', strtotime((string) $post['published_at'])),
            ];
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            . "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n"
                . '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1) . "</loc>\n"
                . (isset($url['lastmod']) ? '    <lastmod>' . $url['lastmod'] . "</lastmod>\n" : '')
                . '    <changefreq>' . $url['freq'] . "</changefreq>\n"
                . '    <priority>' . $url['priority'] . "</priority>\n"
                . "  </url>\n";
        }

        return $xml . "</urlset>\n";
    }
}
