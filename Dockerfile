FROM php:8.1-cli

WORKDIR /app

COPY . .

RUN apt-get update && apt-get install -y unzip git zip libicu-dev libzip-dev libpq-dev \
    && docker-php-ext-install intl pdo pdo_pgsql zip mbstring

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --ignore-platform-reqs --no-interaction --no-dev

EXPOSE 8000

CMD php -S 0.0.0.0:$PORT -t public
