FROM php:8.3-apache

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Instala dependencias
RUN apt-get update && apt-get install -y \
    nano zip unzip git curl libicu-dev libzip-dev libpq-dev \
    && docker-php-ext-install intl pdo pdo_mysql opcache \
    && a2enmod rewrite

# Instala Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Copia los archivos del proyecto
COPY . /var/www/html/

# Corrige permisos
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/vendor

# Establece el DocumentRoot para Symfony (usualmente en /public)
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Expone el puerto 80 (Apache)
EXPOSE 80

# Comando por defecto (apache en modo foreground)
CMD ["apache2-foreground"]
