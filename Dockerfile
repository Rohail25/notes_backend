FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git unzip curl libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chmod -R 777 storage bootstrap/cache

RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan cache:clear

EXPOSE 8080

CMD php -S 0.0.0.0:${PORT:-8080} -t public public/index.php