# Utilisez une image de base PHP avec FPM et Nginx
FROM richarvey/nginx-php-fpm:latest

# Copiez les fichiers de votre application
COPY . /var/www/html

# Installez les dépendances de Composer
RUN composer install --no-dev --optimize-autoloader

# Définissez les permissions appropriées
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exposez le port 80
EXPOSE 80

# Commande de démarrage
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
