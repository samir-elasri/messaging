# docker build -t messaging:1.0 .
# docker run --name messagingContainer -p 8000:8000 messaging:1.0

FROM php:latest
# Install PHP extensions and other dependencies
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev \
        libzip-dev \
        zip \
        unzip \
        mysql-client \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        zip \
        pdo_mysql

# Install Node.js and npm
RUN curl -sL https://deb.nodesource.com/setup_14.x | bash - \
    && apt-get install -y nodejs

# Create the messaging database
# RUN service mysql start \
#     && mysql -u root -e "CREATE DATABASE messaging;"
RUN systemctl start mysql \
    && mysql -u root -e "CREATE DATABASE messaging;"


# Set the working directory to the root of the Laravel application
WORKDIR /var/www/html

# Copy the Laravel application files to the container
COPY . /var/www/html

# Define environment variables
ENV APP_URL=http://localhost/messaging
ENV DB_CONNECTION=mysql
ENV DB_PORT=3306
ENV DB_DATABASE=messaging
ENV DB_USERNAME=root
ENV DB_PASSWORD=

# Install Composer dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer update && composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader

# Install and build Node.js dependencies
RUN npm install && npm run build

# Set the permissions on the storage and bootstrap/cache directories
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Generate the application key
RUN php artisan key:generate

# Migrate the database
RUN php artisan migrate

# Expose port 8000
EXPOSE 8000

# Start Apache and run the application
CMD ["apache2-foreground"]