# Usa una imagen oficial PHP CLI como base
FROM php:8.1-cli

# Directorio de trabajo dentro del contenedor
WORKDIR /app

# Copia todo el proyecto al contenedor
COPY . .

# Instala dependencias del sistema necesarias para Symfony y extensiones PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip git zip libicu-dev libzip-dev libpq-dev libonig-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) intl pdo pdo_pgsql zip mbstring \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instala las dependencias PHP del proyecto sin ejecutar scripts
RUN composer install --ignore-platform-reqs --no-interaction --no-dev --no-scripts

# Expone el puerto que Railway asigna (Railway usa la variable $PORT)
EXPOSE 8000

# Comando para arrancar el servidor embebido PHP en el puerto asignado por Railway
CMD php -S 0.0.0.0:${PORT} -t public
