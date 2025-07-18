#!/usr/bin/env bash

set -o errexit

echo "📦 Installation des dépendances Laravel..."
composer install --no-dev --optimize-autoloader

echo "🔐 Génération de la clé Laravel..."
php artisan key:generate --force

echo "📁 Création du lien vers le dossier storage (si nécessaire)..."
php artisan storage:link || true

echo "⚙️ Cache de la config et des routes..."
php artisan config:cache
php artisan route:cache

echo "🗄️ Exécution des migrations..."
php artisan migrate --force
