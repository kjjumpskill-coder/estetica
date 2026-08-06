<?php
/**
 * @var array<string,mixed> $post
 * @var array<int,array<string,mixed>> $others
 */
use App\Core\Markdown;
use App\Core\Media;

$published = strtotime((string) $post['published_at']);
?>
<article class="section section--top article">
    <div class="wrap">
        <header class="article__head">
            <p class="eyebrow">
                <a href="/blog" style="color:inherit">Блог</a>
                · <time datetime="<?= e(date('Y-m-d', $published)) ?>"><?= e(date('d.m.Y', $published)) ?></time>
            </p>
            <h1 class="section__title"><?= e($post['title']) ?></h1>
            <p class="section__lead"><?= e($post['excerpt']) ?></p>
        </header>

        <?php if (!empty($post['path_base'])): ?>
            <figure class="article__cover">
                <?= $this->raw(Media::picture($post, $post['title'], '(min-width: 900px) 70vw, 100vw', false)) ?>
            </figure>
        <?php endif; ?>

        <div class="article__body">
            <?= $this->raw(Markdown::toHtml((string) $post['body'])) ?>
        </div>

        <div class="article__cta">
            <p>Лишились питання? Напишіть — відповім особисто.</p>
            <a class="btn btn--primary" href="/#zapys">Записатись на консультацію</a>
        </div>

        <?php if ($others !== []): ?>
            <aside class="article__more">
                <h2>Читати далі</h2>
                <ul>
                    <?php foreach ($others as $other): ?>
                        <li>
                            <a href="/blog/<?= e($other['slug']) ?>">
                                <strong><?= e($other['title']) ?></strong>
                                <span><?= e($other['excerpt']) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>
        <?php endif; ?>
    </div>
</article>

<?php
$appUrl = rtrim(\App\Core\Config::str('APP_URL', ''), '/');

$schema = array_filter([
    '@context'      => 'https://schema.org',
    '@type'         => 'Article',
    'headline'      => $post['title'],
    'description'   => $post['excerpt'],
    'datePublished' => date('c', $published),
    'author'        => ['@type' => 'Person', 'name' => setting('master_full_name')],
    'publisher'     => ['@type' => 'Organization', 'name' => setting('brand_name')],
    'image'         => !empty($post['path_base']) ? $appUrl . Media::url($post, 1440) : null,
    'mainEntityOfPage' => $appUrl . '/blog/' . $post['slug'],
]);
?>
<script type="application/ld+json"><?= $this->raw(json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></script>
