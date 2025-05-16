FROM composer:2 AS build

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative


FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    unzip git zip libicu-dev libzip-dev libonig-dev libpng-dev libjpeg-dev libfreetype6-dev \
    nginx curl \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) intl pdo pdo_mysql zip mbstring gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=build /app /var/www/html

RUN rm /etc/nginx/sites-enabled/default
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf

RUN chown -R www-data:www-data /var/www/html

ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=0
ENV APP_ENV=prod

EXPOSE 80

RUN apt-get update && apt-get install -y supervisor && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["/usr/bin/supervisord", "-n"]
