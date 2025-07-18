#!/usr/bin/env bash

echo "Running composer"
composer install --no-dev --working-dir=/var/www/html

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Running migrations..."
php artisan migrate --force

# Si vous utilisez des seeders (facultatif)
echo "Running seeders..."
php artisan db:seed --force

# Optimisation des vues (facultatif)
echo "Optimizing views..."
php artisan view:cache

# Redémarrage du serveur web (si nécessaire, dépend de votre Dockerfile)
#service nginx restart
#service php7.4-fpm restart
