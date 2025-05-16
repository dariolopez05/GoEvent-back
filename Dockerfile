FROM php:8.3-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y apt-utils nano zip unzip git curl \
    && docker-php-ext-install mysqli pdo pdo_mysql

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer \
    && curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
    && apt-get -y install symfony-cli

# Cambiar puerto de escucha Apache según variable PORT de Railway, default 80
ARG PORT=80

RUN sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf \
    && sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Evitar warning de ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

COPY . /var/www/html

RUN mkdir -p var vendor public \
    && chown -R www-data:www-data var vendor public \
    && chmod -R 775 var vendor public

RUN a2enmod rewrite

EXPOSE ${PORT}

CMD ["apache2-foreground"]
