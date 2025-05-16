# ---------- Fase 1: build composer ----------
FROM composer:2 AS build

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative


# ---------- Fase 2: PHP-FPM + NGINX ----------
FROM php:8.1-fpm

# Instalar dependencias PHP
RUN apt-get update && apt-get install -y \
    unzip git zip libicu-dev libzip-dev libonig-dev libpng-dev libjpeg-dev libfreetype6-dev \
    nginx curl \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) intl pdo pdo_mysql zip mbstring gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copiamos el vendor ya generado en build
COPY --from=build /app /var/www/html

# Config nginx
RUN rm /etc/nginx/sites-enabled/default
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

# Permisos correctos
RUN chown -R www-data:www-data /var/www/html || true

# Variables de entorno PHP
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=0
ENV APP_ENV=prod

# Exponer puerto (lo mapea Railway con $PORT)
EXPOSE 9000

# Script de arranque (PHP-FPM + NGINX)
CMD service nginx start && php-fpm
