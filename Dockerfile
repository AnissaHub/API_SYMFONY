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

COPY . .

# --no-scripts : on désactive cache:clear et co au moment du build
RUN composer install --no-dev --optimize-autoloader --no-scripts

EXPOSE 9000

# Le cache sera généré au démarrage du conteneur, quand les vraies env vars sont dispo
CMD php bin/console cache:clear --env=prod --no-debug && php-fpm