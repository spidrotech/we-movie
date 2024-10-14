FROM php:8.3-apache

# Set the working directory
WORKDIR /var/www/symfony

# Install dependencies including MySQL client
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git default-mysql-client && \
    docker-php-ext-install zip pdo pdo_mysql && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Increase PHP memory limit (uncomment if needed)
# RUN echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/memory-limit.ini

# Copy Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install

# Expose port 80 (optional if not using default)
EXPOSE 80
