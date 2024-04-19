# Verwende ein PHP-Apache-Image als Basis
FROM php:apache

# Setze das Arbeitsverzeichnis im Container
WORKDIR /var/www/html

# Kopiere den gesamten Projektordner in das Arbeitsverzeichnis im Container
COPY . .

# Installiere die PHP-Erweiterungen, die Laravel benötigt (Anpassung je nach Bedarf)
RUN docker-php-ext-install pdo pdo_mysql

# Exponiere den Port, auf dem die Anwendung läuft (Standardmäßig Port 80 für Apache)
EXPOSE 8000
