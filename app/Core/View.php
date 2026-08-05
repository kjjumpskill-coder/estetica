<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Рендер PHP-шаблонів. Шаблон отримує змінні як локальні і має доступ
 * до $this->e() та $this->section(). Виводу без екранування в шаблонах бути не має —
 * єдиний виняток це вже підготовлений HTML, і він проходить через raw().
 */
final class View
{
    private string $path;

    /** @var array<string,mixed> */
    private array $data = [];

    public function __construct(?string $path = null)
    {
        $this->path = $path ?? BASE_PATH . '/app/Views';
    }

    /** Екранування для HTML-контексту. */
    public function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** Вставка вже безпечного HTML — викликати лише для власноруч зібраної розмітки. */
    public function raw(?string $html): string
    {
        return (string) $html;
    }

    /** Підключає партіал секції з app/Views/sections. */
    public function section(string $name, array $data = []): void
    {
        echo $this->render('sections/' . $name, $data + $this->data);
    }

    public function partial(string $name, array $data = []): void
    {
        echo $this->render('partials/' . $name, $data + $this->data);
    }

    public function render(string $template, array $data = []): string
    {
        $file = $this->path . '/' . $template . '.php';

        if (!is_file($file)) {
            throw new RuntimeException("Немає шаблону: {$template}");
        }

        $this->data = $data + $this->data;

        extract($data, EXTR_SKIP);
        ob_start();

        try {
            require $file;
        } catch (\Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return (string) ob_get_clean();
    }

    /** Рендерить сторінку всередині layout'а. */
    public function page(string $template, array $data = [], string $layout = 'layouts/base'): string
    {
        $content = $this->render($template, $data);

        return $this->render($layout, $data + ['content' => $content]);
    }
}
