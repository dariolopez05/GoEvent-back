FROM php:8.1-cli

WORKDIR /app

COPY . .

RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip git zip libicu-dev libzip-dev libpq-dev libonig-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) intl pdo pdo_pgsql zip mbstring \
    && apt-get clean && rm -rf /var/lib/apt/lists/*


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --ignore-platform-reqs --no-interaction --no-dev

EXPOSE 8000

CMD php -S 0.0.0.0:$PORT -t public
