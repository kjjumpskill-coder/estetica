<?php
/**
 * Блог — три останні статті.
 * Поки записів немає, секція не малює порожню сітку, а просто не виводиться.
 *
 * @var array<int,array<string,mixed>> $posts
 */
$posts = $posts ?? [];

if ($posts === []) {
    return;
}
?>
<section class="section" id="blog">
    <div class="wrap">
        <div class="section__head" data-reveal>
            <p class="eyebrow">Блог</p>
            <h2 class="section__title">Корисне про догляд і процедури</h2>
        </div>

        <ul class="posts">
            <?php foreach ($posts as $i => $post): ?>
                <li class="post" data-reveal style="--reveal-delay: <?= $i * 70 ?>ms">
                    <a href="/blog/<?= e($post['slug']) ?>">
                        <?php if (!empty($post['path_base'])): ?>
                            <?= $this->raw(\App\Core\Media::picture($post, $post['title'], '(min-width: 768px) 32vw, 100vw')) ?>
                        <?php endif; ?>
                        <time datetime="<?= e(date('Y-m-d', strtotime((string) $post['published_at']))) ?>">
                            <?= e(date('d.m.Y', strtotime((string) $post['published_at']))) ?>
                        </time>
                        <h3><?= e($post['title']) ?></h3>
                        <p><?= e($post['excerpt']) ?></p>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="center-action">
            <a class="btn btn--ghost" href="/blog">Усі статті</a>
        </div>
    </div>
</section>
