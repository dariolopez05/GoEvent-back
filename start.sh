#!/bin/bash
set -e

echo "Valor de DATABASE_URL: $DATABASE_URL"
echo "Usando puerto: $PORT"

echo "Esperando base de datos..."

until php test-db.php | grep -q "OK"; do
  echo "Base de datos no disponible, esperando 5 segundos..."
  sleep 5
done

echo "Ejecutando migraciones..."
APP_ENV=prod php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "Iniciando servidor..."
APP_ENV=prod php -S 0.0.0.0:${PORT} -t public
