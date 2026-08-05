# Версії навмисно збігаються з shared-хостингом ukraine.com.ua,
# щоб локальна поведінка відповідала бойовій.
FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        libfreetype6-dev libjpeg62-turbo-dev libpng-dev libwebp-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" gd pdo_mysql \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite headers expires deflate

# Document root — public_html, решта проєкту лишається поза вебом.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public_html
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf

WORKDIR /var/www/html
