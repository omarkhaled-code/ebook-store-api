#!/bin/bash

# Run migrations and seeder
php artisan migrate --force
php artisan db:seed --class=ProductionSeeder --force

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set Apache port
sed -i "s/Listen 80/Listen 8080/" /etc/apache2/ports.conf
sed -i "s/Listen 443//" /etc/apache2/ports.conf

# Start Apache
apache2-foreground