<?php
/**
 * Мікророзмітка schema.org.
 *
 * BeautySalon з двома філіями, Person для майстра, Service для кожної послуги,
 * FAQPage для питань. Відгуки додаються до Review лише тоді, коли в них є текст:
 * порожній Review без body — привід для попередження в Search Console.
 *
 * @var array<string,array<int,array<string,mixed>>> $services
 * @var array<string,array<int,array<string,mixed>>> $faq
 * @var array<int,array<string,mixed>> $reviews
 */
use App\Core\Config;

$appUrl = rtrim(Config::str('APP_URL', ''), '/');
$city   = setting('city', 'Дніпро');

$branches = [];

foreach ([1, 2] as $n) {
    if (!has_setting("loc{$n}_address")) {
        continue;
    }

    $branch = [
        '@type' => 'BeautySalon',
        'name'  => setting('brand_name') . ' — ' . setting("loc{$n}_title"),
        'address' => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => setting("loc{$n}_address"),
            'addressLocality' => $city,
            'addressCountry'  => 'UA',
        ],
    ];

    if (has_setting("loc{$n}_lat")) {
        $branch['geo'] = [
            '@type'     => 'GeoCoordinates',
            'latitude'  => (float) setting("loc{$n}_lat"),
            'longitude' => (float) setting("loc{$n}_lng"),
        ];
    }

    if (has_setting('phone')) {
        $branch['telephone'] = setting('phone');
    }

    $branches[] = $branch;
}

$serviceList = [];

foreach ($services as $items) {
    foreach ($items as $s) {
        $serviceList[] = array_filter([
            '@type'       => 'Service',
            'name'        => $s['title'],
            'description' => $s['short_desc'],
            'serviceType' => 'BeautySalon',
        ]);
    }
}

$faqEntities = [];

foreach ($faq as $items) {
    foreach ($items as $item) {
        $faqEntities[] = [
            '@type'          => 'Question',
            'name'           => $item['question'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => trim(preg_replace('/\s+/u', ' ', $item['answer']) ?? ''),
            ],
        ];
    }
}

$graph = [];

$salon = array_filter([
    '@type'       => 'BeautySalon',
    '@id'         => $appUrl . '/#salon',
    'name'        => setting('brand_name'),
    'description' => 'Перманентний макіяж та ін’єкційна косметологія у місті ' . $city,
    'url'         => $appUrl ?: null,
    'telephone'   => has_setting('phone') ? setting('phone') : null,
    'email'       => has_setting('email') ? setting('email') : null,
    'areaServed'  => $city,
    'department'  => $branches ?: null,
    'sameAs'      => array_values(array_filter([
        setting('instagram_url'),
        setting('facebook_url'),
        setting('tiktok_url'),
    ])),
    'hasOfferCatalog' => $serviceList === [] ? null : [
        '@type'           => 'OfferCatalog',
        'name'            => 'Послуги',
        'itemListElement' => $serviceList,
    ],
]);

$graph[] = $salon;

$graph[] = array_filter([
    '@type'      => 'Person',
    '@id'        => $appUrl . '/#master',
    'name'       => setting('master_full_name'),
    'jobTitle'   => setting('master_role'),
    'worksFor'   => ['@id' => $appUrl . '/#salon'],
    'knowsAbout' => ['перманентний макіяж', 'ін’єкційна косметологія', 'догляд за шкірою'],
]);

if ($faqEntities !== []) {
    $graph[] = [
        '@type'      => 'FAQPage',
        'mainEntity' => $faqEntities,
    ];
}

foreach ($reviews as $r) {
    if (trim((string) ($r['body'] ?? '')) === '') {
        continue;
    }

    $graph[] = array_filter([
        '@type'         => 'Review',
        'itemReviewed'  => ['@id' => $appUrl . '/#salon'],
        'author'        => ['@type' => 'Person', 'name' => $r['author_name']],
        'reviewBody'    => trim(preg_replace('/\s+/u', ' ', (string) $r['body']) ?? ''),
        'datePublished' => $r['review_date'] ?: null,
    ]);
}

$json = json_encode(
    ['@context' => 'https://schema.org', '@graph' => $graph],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
?>
<script type="application/ld+json"><?= $this->raw($json) ?></script>
