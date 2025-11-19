FROM php:8.2-apache

# Installer l'extension mysqli pour la connexion à la base de données MySQL
RUN docker-php-ext-install mysqli
RUN docker-php-ext-enable mysqli

# Copier l'application dans le répertoire web du conteneur
COPY . /var/www/html/

# Exposer le port 80
EXPOSE 80
