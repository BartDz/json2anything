FROM php:8.3-apache

RUN apt-get update && apt-get install -y libyaml-dev git unzip libzip-dev \
    && pecl install yaml \
    && docker-php-ext-enable yaml \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

COPY apache.conf /etc/apache2/sites-available/000-default.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
