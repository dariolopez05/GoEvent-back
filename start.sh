#!/bin/bash
set -e

echo "Valor de DATABASE_URL: $DATABASE_URL"
export DATABASE_URL="$DATABASE_URL"

echo "Esperando base de datos..."
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
  echo "Base de datos no disponible, esperando 5 segundos..."
  sleep 5
done

echo "Ejecutando migraciones..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "Iniciando servidor..."
php -S 0.0.0.0:${PORT} -t public
