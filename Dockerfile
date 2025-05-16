FROM php:8.3-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y apt-utils nano zip unzip git curl \
    && docker-php-ext-install mysqli pdo pdo_mysql

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

# Configurar apache para apuntar a /public y permitir rewrite
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN echo '<Directory /var/www/html/public>\nAllowOverride All\n</Directory>' >> /etc/apache2/apache2.conf

RUN a2enmod rewrite

# Establecer un ServerName genérico
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf

COPY . /var/www/html

RUN mkdir -p var vendor public \
    && chown -R www-data:www-data var vendor public \
    && chmod -R 775 var vendor public

ENV APACHE_RUN_USER=www-data
ENV APACHE_RUN_GROUP=www-data
ENV APACHE_LOG_DIR=/var/log/apache2
ENV APACHE_PID_FILE=/var/run/apache2/apache2.pid
ENV APACHE_RUN_DIR=/var/run/apache2
ENV APACHE_LOCK_DIR=/var/lock/apache2
ENV PORT=80
EXPOSE ${PORT}

CMD ["apache2-foreground"]
