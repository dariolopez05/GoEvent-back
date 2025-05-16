FROM php:8.3-apache

# Instala dependencias necesarias y extensiones PHP
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl nano \
    && docker-php-ext-install pdo pdo_mysql zip mysqli \
    && a2enmod rewrite

# Configura DocumentRoot para Symfony (carpeta public)
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Establece directorio de trabajo
WORKDIR /var/www/html

# Copia archivos de la app (asumiendo que el Dockerfile está en la raíz del proyecto)
COPY . .

# Instala Composer (si lo necesitas para dependencias, opcional si ya instalaste antes)
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# Instala dependencias PHP sin paquetes de desarrollo y optimiza autoload
RUN composer install --no-dev --optimize-autoloader

# Da permisos a var y vendor para Apache (www-data)
RUN chown -R www-data:www-data var vendor public \
    && chmod -R 775 var vendor public

# Limpia cache Symfony para producción
RUN php bin/console cache:clear --env=prod --no-debug

# Expone puerto 80 para Railway
EXPOSE 80

# Comando para arrancar Apache en primer plano
CMD ["apache2-foreground"]
