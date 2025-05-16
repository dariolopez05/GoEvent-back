FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    unzip git curl libicu-dev libzip-dev libpq-dev \
    && docker-php-ext-install intl pdo pdo_mysql opcache \
    && a2enmod rewrite

WORKDIR /var/www/html

COPY . /var/www/html

# Instala composer y dependencias
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Cambiar permisos (si es necesario)
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/vendor

# Cambiar document root de Apache a /var/www/html/public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"]
