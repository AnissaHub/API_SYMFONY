FROM php:8.2-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libxml2-dev \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    intl \
    zip \
    ctype \
    xml

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ENV COMPOSER_MEMORY_LIMIT=-1
ENV APP_ENV=prod

COPY . .

RUN rm -rf var/cache/* var/log/*

RUN composer install --optimize-autoloader --no-scripts

EXPOSE 9000

CMD php bin/console cache:clear --env=prod --no-debug && php-fpm