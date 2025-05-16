FROM php:8.3-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    nano zip unzip git curl libicu-dev libzip-dev libpq-dev \
    && docker-php-ext-install intl pdo pdo_mysql opcache \
    && a2enmod rewrite

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

COPY . /var/www/html/

RUN if [ -d /var/www/html/var ]; then chown -R www-data:www-data /var/www/html/var; fi
RUN if [ -d /var/www/html/vendor ]; then chown -R www-data:www-data /var/www/html/vendor; fi


RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"]