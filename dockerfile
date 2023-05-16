# Stage 1: Build Laravel assets with Vite
FROM node:14 as build

WORKDIR /var/www/html

# Copy package.json and package-lock.json
COPY package.json package-lock.json /var/www/html/

# Install dependencies
RUN npm ci

# Copy the rest of the Laravel application
COPY . /var/www/html/

# Build assets
RUN npm run build

# Stage 2: PHP and Apache setup
FROM php:8.0-apache

# Install PHP extensions and other dependencies
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev \
        libzip-dev \
        zip \
        unzip \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        zip

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy Laravel files
COPY --from=build /var/www/html /var/www/html

# Set the document root
RUN sed -i -e 's/html$/html\/public/g' /etc/apache2/sites-available/000-default.conf

# Set the Laravel storage and bootstrap cache directories permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Laravel dependencies
RUN composer install --optimize-autoloader --no-dev

# Set up environment variables
COPY .env.example /var/www/html/.env
RUN php artisan key:generate

# Generate the optimized class loader
RUN php artisan optimize

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
