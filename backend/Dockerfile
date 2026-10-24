# PHP-FPM base compartida por todas las etapas, extensiones idénticas
FROM php:8.4-fpm-alpine AS php_base
RUN apk add --no-cache libxml2 oniguruma \
    && apk add --no-cache --virtual .php-build-deps $PHPIZE_DEPS libxml2-dev oniguruma-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring xml opcache \
    && apk del .php-build-deps
WORKDIR /var/www/html

# Se instala Composer solo en la etapa de dependencias
FROM php_base AS dependencies
RUN apk add --no-cache git unzip
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --no-autoloader

# Composer genera autoload con el código completo
FROM dependencies AS builder
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-scripts

# Imagen final: sin Composer, git, unzip ni dependencias dev
FROM php_base AS runtime
COPY . .
COPY --from=builder /var/www/html/vendor ./vendor
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
USER www-data
EXPOSE 9000
CMD ["php-fpm"]
