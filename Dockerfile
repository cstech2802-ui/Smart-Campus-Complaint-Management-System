FROM php:8.2-apache

# Install PostgreSQL extensions for PHP PDO communication
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Enable Apache rewrite engine modules
RUN a2enmod rewrite

# Copy all application repository code into the web server directory
COPY . /var/www/html/

# Expose port 80 for web traffic handling
EXPOSE 80
