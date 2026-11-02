FROM php:8.3-cli-alpine

# Install system dependencies & PHP extensions
RUN apk add --no-cache \
    curl \
    git \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite-dev \
    mysql-client \
    oniguruma-dev

RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application files (including pre-compiled frontend assets in public/build)
COPY . /var/www

# Prepare environment and install PHP dependencies cleanly
RUN cp .env.example .env && \
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --no-scripts --ignore-platform-reqs && \
    composer dump-autoload --optimize

EXPOSE 8000

CMD ["sh", "-c", "touch database/database.sqlite && php artisan key:generate --force && php artisan migrate --force && php artisan storage:link && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
