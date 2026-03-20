FROM php:8.2-cli

# Install dependencies
RUN apt-get update && apt-get install -y \
    git unzip curl libpq-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

ENV COMPOSER_MEMORY_LIMIT=-1

# Install PHP dependencies without running artisan scripts at build time
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Fix permissions
RUN chmod -R 777 storage bootstrap/cache

EXPOSE 8000

# ❌ IMPORTANT: use Railway dynamic port via sh -c
CMD sh -c "php artisan config:clear && php artisan cache:clear && php -S 0.0.0.0:${PORT} -t public"