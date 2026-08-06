<?php
/** @var array<int,array<string,mixed>> $posts */
use App\Core\Media;
?>
<section class="section section--top">
    <div class="wrap">
        <div class="section__head">
            <p class="eyebrow">Блог</p>
            <h1 class="section__title">Корисне про догляд і процедури</h1>
            <p class="section__lead">
                Те, що я найчастіше пояснюю на консультаціях — тільки докладніше,
                ніж встигаю розповісти за пів години.
            </p>
        </div>

        <?php if ($posts === []): ?>
            <p class="empty-note">Статей поки немає.</p>
        <?php else: ?>
            <ul class="posts posts--list">
                <?php foreach ($posts as $post): ?>
                    <li class="post">
                        <a href="/blog/<?= e($post['slug']) ?>">
                            <?php if (!empty($post['path_base'])): ?>
                                <?= $this->raw(Media::picture($post, $post['title'], '(min-width: 768px) 32vw, 100vw')) ?>
                            <?php endif; ?>
                            <time datetime="<?= e(date('Y-m-d', strtotime((string) $post['published_at']))) ?>">
                                <?= e(date('d.m.Y', strtotime((string) $post['published_at']))) ?>
                            </time>
                            <h2><?= e($post['title']) ?></h2>
                            <p><?= e($post['excerpt']) ?></p>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="center-action">
            <a class="btn btn--ghost" href="/">На головну</a>
        </div>
    </div>
</section>
