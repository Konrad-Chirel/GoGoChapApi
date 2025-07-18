# Utilisez une image de base PHP avec FPM et Nginx
FROM richarvey/nginx-php-fpm:latest

# Copiez les fichiers de votre application
COPY . /var/www/html

# Définissez le répertoire de travail
WORKDIR /var/www/html

# Assurez-vous que Composer est globalement accessible et que PHP est dans le PATH
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV PATH="$PATH:/usr/local/bin:/usr/bin"

# Installez les dépendances de Composer
RUN composer install --no-dev --optimize-autoloader

# Définissez les permissions appropriées
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Exposez le port 80
EXPOSE 80

# Commande de démarrage
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
