FROM php:8.3-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y apt-utils zip unzip git curl nano \
    && docker-php-ext-install pdo pdo_mysql mysqli

COPY . /var/www/html

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader

RUN mkdir -p var vendor public \
    && chown -R www-data:www-data var vendor public \
    && chmod -R 775 var vendor public

RUN a2enmod rewrite

RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

ENV PORT 8080

# Establece ServerName para evitar warning
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Cambia el puerto de Apache al puerto de Railway (variable PORT)
RUN sed -i "s/Listen 80/Listen ${PORT:-80}/" /etc/apache2/ports.conf
RUN sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT:-80}>/" /etc/apache2/sites-available/000-default.conf

EXPOSE $PORT

CMD apache2ctl -D FOREGROUND -k start -f /etc/apache2/apache2.conf -c "Listen ${PORT:-80}"
