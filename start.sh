#!/bin/bash

# Cambiar el puerto de Apache de 80 a 9000
sed -i 's/Listen 80/Listen 9000/' /etc/apache2/ports.conf
sed -i 's/<VirtualHost \*:80>/<VirtualHost *:9000>/' /etc/apache2/sites-enabled/000-default.conf

# Arrancar Apache en primer plano
apache2-foreground
