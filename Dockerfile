FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git unzip curl libpq-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-scripts

RUN chmod -R 777 storage bootstrap/cache

EXPOSE 8080

CMD ["sh", "-c", "php artisan config:clear && php artisan route:clear && php artisan cache:clear && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]