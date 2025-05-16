FROM php:8.3-apache
WORKDIR /var/www/html

RUN apt-get update
RUN apt-get -y install apt-utils nano zip unzip git curl
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

# Symfony CLI (opcional si usas)
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash
RUN apt-get -y install symfony-cli

COPY . /var/www/html

RUN composer install --no-dev --optimize-autoloader

# Da permisos para Symfony cache y logs (si es necesario)
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/vendor || true

EXPOSE 80

CMD ["apache2-foreground"]

COPY start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]

RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Activa reescritura (Symfony la necesita)
RUN a2enmod rewrite

# Configura AllowOverride para .htaccess de Symfony
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf