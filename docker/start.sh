#!/bin/bash
set -e

# Ensure permissions at container start (safeguard)
mkdir -p /var/www/html/storage/framework/views /var/www/html/storage/framework/cache/data /var/www/html/storage/framework/sessions /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Optional DB wait (enable by setting WAIT_FOR_DB=true in env)
if [ "${WAIT_FOR_DB:-false}" = "true" ]; then
  echo "Waiting for DB to be available..."
  until php -r "new PDO('${DB_CONNECTION:-mysql}:host=${DB_HOST:-127.0.0.1};port=${DB_PORT:-3306}','${DB_USERNAME:-root}','${DB_PASSWORD:-}');" >/dev/null 2>&1; do
    sleep 1
  done
fi

# Warn when APP_KEY missing
if [ -z "$APP_KEY" ]; then
  echo "Warning: APP_KEY is not set. It's strongly recommended to set APP_KEY in environment variables."
fi

# Optional automatic migrations (enable by setting MIGRATE=true)
if [ "${MIGRATE:-false}" = "true" ]; then
  echo "Running migrations..."
  php artisan migrate --force
fi

# Run caches at runtime so they pick up Render environment variables
if [ "${SKIP_OPTIMIZE:-false}" != "true" ]; then
  echo "Running artisan optimization (config/route/view cache)..."
  php artisan config:cache || true
  php artisan route:cache || true
  php artisan view:cache || true
fi

# Start Apache in foreground
exec apache2-foreground
