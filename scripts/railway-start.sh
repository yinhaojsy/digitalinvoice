#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

mkdir -p \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache \
  database

chmod -R ug+rwx storage bootstrap/cache || true

# Ensure sqlite file exists when using default connection
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
  touch database/database.sqlite
fi

# Create a minimal .env so artisan can boot if Railway only injects process env
if [ ! -f .env ]; then
  if [ -z "${APP_KEY:-}" ]; then
    # Temporary key for first boot (set a real APP_KEY in Railway Variables)
    GENERATED_KEY="base64:$(head -c 32 /dev/urandom | base64)"
  else
    GENERATED_KEY="$APP_KEY"
  fi

  cat > .env <<EOF
APP_NAME="${APP_NAME:-Digital Invoicing}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${GENERATED_KEY}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-http://localhost}"
LOG_CHANNEL="${LOG_CHANNEL:-stderr}"
LOG_LEVEL="${LOG_LEVEL:-debug}"
DB_CONNECTION="${DB_CONNECTION:-sqlite}"
DB_DATABASE="${DB_DATABASE:-database/database.sqlite}"
SESSION_DRIVER="${SESSION_DRIVER:-file}"
CACHE_STORE="${CACHE_STORE:-file}"
QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"
EOF
fi

php artisan migrate --force --no-interaction
php artisan config:clear
php artisan route:clear
php artisan view:clear

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
