#!/bin/bash
set -e

# Runs as root so we can fix ownership on volume-mounted directories before
# PHP-FPM starts workers as the webapp user (PHP_FPM_USER / PHP_FPM_GROUP).
PHP_UID="${PHP_FPM_USER:-1000}"
PHP_GID="${PHP_FPM_GROUP:-1000}"

mkdir -p \
    /app/storage/app/public \
    /app/storage/framework/cache/data \
    /app/storage/framework/sessions \
    /app/storage/framework/testing \
    /app/storage/framework/views \
    /app/storage/logs \
    /app/bootstrap/cache

chown -R "${PHP_UID}:${PHP_GID}" /app/storage /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache

exec /entrypoint.sh "$@"
