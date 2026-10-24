#!/bin/sh
set -eu

port="${PORT:-10000}"
sed -i "s/^Listen 80$/Listen ${port}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${port}>/" /etc/apache2/sites-available/000-default.conf

php artisan package:discover --ansi

attempt=0
until php artisan migrate --force; do
    attempt=$((attempt + 1))
    if [ "$attempt" -ge 5 ]; then
        echo "Database migrations failed after five attempts." >&2
        exit 1
    fi
    sleep 5
done

php artisan db:seed --class=AdminUserSeeder --force
php artisan db:seed --class=WebsiteSeeder --force

chown -R www-data:www-data storage bootstrap/cache
exec apache2-foreground
