FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git unzip curl libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

# Fix permissions
RUN chmod -R 777 storage bootstrap/cache

# ❌ REMOVE artisan commands from build (important)

EXPOSE 8080

# ✅ Use Railway PORT correctly
CMD php -S 0.0.0.0:$PORT -t public