# Use official PHP image with Apache preinstalled
FROM php:8.2-apache

# Enable Apache mod_rewrite (required for pretty URLs)
RUN a2enmod rewrite

# Allow .htaccess to override settings
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Set working directory inside the container
WORKDIR /var/www/html

# Copy all project files into the container
COPY . /var/www/html/

# Install dependencies and Composer
RUN apt-get update && apt-get install -y unzip git \
    && docker-php-ext-install pdo pdo_mysql \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

# Install PHP dependencies using Composer
RUN composer install --no-dev --optimize-autoloader || true

# Set the public folder as Apache’s document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Update Apache configuration for the new document root
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# ✅ Ensure writable permission for the data directory
RUN mkdir -p /var/www/html/data \
    && chown -R www-data:www-data /var/www/html/data \
    && chmod -R 775 /var/www/html/data

# Expose Apache port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
