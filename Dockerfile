FROM php:8.3-apache

# Establece el directorio de trabajo
WORKDIR /var/www/html

# Instala dependencias
RUN apt-get update && apt-get -y install apt-utils nano zip unzip git curl \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Composer y Symfony CLI
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer \
    && curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash \
    && apt-get -y install symfony-cli

# Cambia el DocumentRoot de Apache a /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Copia el código de tu proyecto al contenedor
COPY . /var/www/html

# Asigna permisos correctos (evita errores de cache/logs)
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/vendor || true

# Habilita mod_rewrite de Apache (para las URLs bonitas de Symfony)
RUN a2enmod rewrite

# Expone el puerto
EXPOSE 80
