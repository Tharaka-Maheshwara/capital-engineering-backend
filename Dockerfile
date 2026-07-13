# Multi-stage Dockerfile for Laravel (PHP 8.2 + Apache) optimized for production
FROM composer:2 AS builder

WORKDIR /app

# Copy the Laravel files needed by Composer scripts before install runs.
COPY composer.json composer.lock artisan /app/
COPY bootstrap /app/bootstrap
COPY config /app/config
COPY app /app/app
COPY routes /app/routes
COPY database /app/database
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress

# Copy application source
COPY . /app

# Dump optimized autoloader
RUN composer dump-autoload -o

### Production image
FROM php:8.2-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Change Apache document root to Laravel public folder
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Install system dependencies required for PHP extensions and common tools
RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    build-essential \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libxml2-dev \
    libicu-dev \
    pkg-config \
    libonig-dev \
    libssl-dev \
 && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions used by Laravel + MySQL
RUN docker-php-ext-configure gd --with-jpeg --with-freetype --with-webp \
 && docker-php-ext-install -j$(nproc) pdo pdo_mysql mbstring xml zip bcmath gd opcache intl pcntl

# Install Redis (optional, common for queues/cache)
RUN pecl install redis || true && docker-php-ext-enable redis || true

# Enable common Apache modules
RUN a2enmod rewrite headers expires

# Copy Composer from builder image (already installed in builder)
COPY --from=builder /usr/bin/composer /usr/bin/composer

# Copy application files from builder
COPY --from=builder /app /var/www/html

# Add entrypoint script
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Ensure correct permissions for Laravel storage and cache
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html \
 && mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
 && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
 && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 80 for HTTP (Render expects 80 by default)
EXPOSE 80

# Start Apache via our entrypoint
CMD ["/usr/local/bin/start.sh"]
