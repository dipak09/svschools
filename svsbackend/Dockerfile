# syntax=docker/dockerfile:1

# ---------- Stage 1: front-end assets (Vite / Tailwind) ----------
FROM node:20-alpine AS assets

WORKDIR /app

COPY package.json vite.config.js ./
RUN npm install

COPY resources ./resources
RUN npm run build

# ---------- Stage 2: PHP dependencies ----------
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader

# ---------- Stage 3: runtime (Apache2 + PHP 8.2) ----------
FROM php:8.2-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public \
    APACHE_RUN_USER=www-data \
    APACHE_RUN_GROUP=www-data

# System packages needed to build the PHP extensions this app uses
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# pdo_mysql -> MySQL, redis -> REDIS_CLIENT=phpredis, the rest are Laravel staples
RUN docker-php-ext-install -j"$(nproc)" pdo_mysql mysqli zip intl bcmath opcache \
    && pecl install redis \
    && docker-php-ext-enable redis

# Apache: rewrite for Laravel's front controller, headers for common middleware
RUN a2enmod rewrite headers

COPY docker/apache/ports.conf /etc/apache2/ports.conf
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini

# Composer binary kept around for artisan-adjacent work inside the container
COPY --from=vendor /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Application code, then the artefacts built in the earlier stages
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --no-dev --optimize --no-interaction \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 8000

ENTRYPOINT ["entrypoint"]
CMD ["apache2-foreground"]
