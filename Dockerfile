FROM php:8.2-cli-alpine

# Install system dependencies & PHP extensions
RUN apk add --no-cache \
    curl \
    git \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    mysql-client \
    oniguruma-dev \
    nodejs \
    npm

RUN docker-php-ext-install pdo pdo_mysql mbstring zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . /var/www

RUN composer install --no-interaction --prefer-dist --optimize-autoloader
RUN npm install && npm run build

EXPOSE 8000

CMD ["sh", "-c", "php artisan migrate --force && php artisan storage:link && php artisan serve --host=0.0.0.0 --port=8000"]
