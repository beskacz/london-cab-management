#!/bin/sh
set -e
cd /var/www/html

if [ ! -f vendor/autoload.php ]; then
    echo "gibbon-docker: running composer install..."
    composer install --no-interaction --prefer-dist --no-progress
fi

mkdir -p resources/templates/cache var uploads
chmod -R a+rwX uploads var resources/templates/cache 2>/dev/null || true

exec apache2-foreground
