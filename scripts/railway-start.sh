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

# Railway exposes DATABASE_URL; Laravel reads DB_URL
export DB_URL="${DB_URL:-${DATABASE_URL:-}}"

# Prefer Postgres URL connection — unset stray SQLite defaults that break pgsql
if [ -n "${DB_URL}" ] || [ "${DB_CONNECTION:-}" = "pgsql" ]; then
  export DB_CONNECTION="${DB_CONNECTION:-pgsql}"
  unset DB_DATABASE || true
  unset DB_HOST || true
fi

# Ensure sqlite file exists only when truly using sqlite
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ] && [ -z "${DB_URL:-}" ]; then
  touch database/database.sqlite
fi

# Minimal .env for APP_KEY only — do NOT hardcode DB_* (Railway vars win)
if [ ! -f .env ]; then
  if [ -z "${APP_KEY:-}" ]; then
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
SESSION_DRIVER="${SESSION_DRIVER:-file}"
CACHE_STORE="${CACHE_STORE:-file}"
QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"
EOF
fi

# Drop stale sqlite DB path from an old .env if present
if [ -f .env ] && { [ -n "${DB_URL:-}" ] || [ "${DB_CONNECTION:-}" = "pgsql" ]; }; then
  sed -i '/^DB_DATABASE=/d' .env || true
  sed -i '/^DB_HOST=/d' .env || true
  if ! grep -q '^DB_CONNECTION=' .env 2>/dev/null; then
    echo "DB_CONNECTION=pgsql" >> .env
  else
    sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=pgsql/' .env || true
  fi
  if [ -n "${DB_URL:-}" ]; then
    sed -i '/^DB_URL=/d' .env || true
    sed -i '/^DATABASE_URL=/d' .env || true
    # Escape carefully: write via env, not echoing raw password into logs
    printf 'DB_URL=%s\n' "$DB_URL" >> .env
  fi
fi

php artisan config:clear
php artisan migrate --force --no-interaction
php artisan route:clear
php artisan view:clear

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8000}"
