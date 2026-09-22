#!/bin/bash
set -e

cd /var/www/html

# The image ships without a .env (it is dockerignored); seed one from the
# container template so artisan has a file to work with. Real environment
# variables from docker-compose still take precedence over this file.
if [ ! -f .env ] && [ -f .env.docker ]; then
	echo "[entrypoint] creating .env from .env.docker"
	cp .env.docker .env
fi

if ! grep -qE '^APP_KEY=base64:' .env 2>/dev/null && [ -z "${APP_KEY:-}" ]; then
	echo "[entrypoint] generating application key"
	php artisan key:generate --force --no-interaction
fi

# Wait for MySQL before touching the database
if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
	DB_WAIT_HOST="${DB_HOST:-db}"
	DB_WAIT_PORT="${DB_PORT:-3306}"
	echo "[entrypoint] waiting for mysql at ${DB_WAIT_HOST}:${DB_WAIT_PORT}"
	for i in $(seq 1 60); do
		if mysqladmin ping -h "$DB_WAIT_HOST" -P "$DB_WAIT_PORT" --silent >/dev/null 2>&1; then
			echo "[entrypoint] mysql is up"
			break
		fi
		if [ "$i" = "60" ]; then
			echo "[entrypoint] mysql did not become ready in time" >&2
			exit 1
		fi
		sleep 2
	done
fi

# Storage may be a volume mount, so fix ownership at boot, not only at build
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

php artisan config:clear --no-interaction

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
	echo "[entrypoint] running migrations"
	php artisan migrate --force --no-interaction
fi

# After migrations: with CACHE_STORE=database the cache table has to exist first
php artisan cache:clear --no-interaction || true
php artisan view:clear --no-interaction || true

exec "$@"
