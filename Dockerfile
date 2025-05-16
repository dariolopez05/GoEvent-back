FROM php:8.1-cli

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip git zip libicu-dev libzip-dev libonig-dev default-mysql-client \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) intl pdo pdo_mysql zip mbstring \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN rm -rf var/cache/*

RUN composer install --no-dev --optimize-autoloader --no-interaction -vvv

RUN php bin/console cache:clear --env=prod --no-warmup
RUN php bin/console cache:warmup --env=prod

EXPOSE 8000

CMD ["php", "-d", "display_errors=1", "-d", "error_reporting=E_ALL", "-S", "0.0.0.0:8000", "-t", "public"]