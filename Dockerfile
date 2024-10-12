FROM php:8.3-apache

# Set the working directory
WORKDIR /var/www/symfony

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git && \
    docker-php-ext-install zip pdo pdo_mysql

# Increase PHP memory limit
#RUN echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/memory-limit.ini

# Copy Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install

# Expose port 80 (optional if not using default)
EXPOSE 80
