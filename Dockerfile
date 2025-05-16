# PHP-FPM image (8.2)
FROM php:8.2-fpm-alpine AS app

# Install system dependencies
RUN apk add --no-cache \
    bash \
    git \
    icu-dev \
    libxml2-dev \
    oniguruma-dev \
    zlib-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    postgresql-dev \
    && docker-php-ext-install intl pdo pdo_pgsql opcache xml zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy app files
COPY . .

# Install PHP dependencies (production)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --verbose

# NGINX image (as webserver)
FROM nginx:stable-alpine AS webserver

# Copy NGINX config template
COPY docker/nginx.conf.template /etc/nginx/nginx.conf.template

# Generate final nginx.conf with PORT from env (default to 8080 if not set)
RUN export PORT=${PORT:-8080} && \
    envsubst '$PORT' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

# Copy built PHP app to NGINX container
COPY --from=app /var/www /var/www

# Expose port (default 8080, but can be overwritten by Railway)
EXPOSE 8080

# Start NGINX in foreground
CMD ["nginx", "-g", "daemon off;"]
