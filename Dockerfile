FROM php:8.4-cli-alpine

# Install system dependencies & PHP extensions
RUN apk add --no-cache \
    curl \
    git \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite \
    sqlite-dev \
    mysql-client \
    ca-certificates \
    oniguruma-dev

RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring zip bcmath pcntl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application files (including pre-compiled frontend assets in public/build)
COPY . /var/www

# Prepare environment and install PHP dependencies cleanly with --no-scripts
RUN cp .env.example .env && \
    touch database/database.sqlite && \
    chmod -R 777 storage bootstrap/cache database && \
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --no-scripts --ignore-platform-reqs

EXPOSE 8000

CMD ["sh", "-c", "php artisan migrate --force && php artisan storage:link && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
