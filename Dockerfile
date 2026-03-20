FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git unzip curl libpq-dev libzip-dev nginx supervisor \
    && docker-php-ext-install pdo pdo_pgsql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Fix permissions
RUN chmod -R 777 storage bootstrap/cache

# Copy Nginx config
COPY docker/nginx.conf /etc/nginx/sites-available/default

# Expose any port (Railway will use $PORT)
EXPOSE 1518

# Start supervisor to run php-fpm + nginx
CMD ["/usr/bin/supervisord", "-n"]