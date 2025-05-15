FROM php:8.1-fpm

# Instala dependencias necesarias para Symfony y Composer
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install intl pdo pdo_pgsql zip opcache

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install --ignore-platform-reqs --no-interaction --no-scripts --no-dev

COPY . .

CMD ["php-fpm"]
