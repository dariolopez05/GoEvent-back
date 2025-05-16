FROM php:8.3-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get -y install apt-utils nano zip unzip git curl \
    && docker-php-ext-install mysqli pdo pdo_mysql

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer \
    && curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
    && apt-get -y install symfony-cli

RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html

RUN mkdir -p /var/www/html/var /var/www/html/vendor \
    && chown -R www-data:www-data /var/www/html/var /var/www/html/vendor \
    || true

RUN a2enmod rewrite

EXPOSE 80

CMD ["apache2-foreground"]
