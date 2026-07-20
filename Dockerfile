FROM php:8.2-fpm

WORKDIR /var/www

# Installer les dépendances système + extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    intl \
    zip

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copier le code du projet
COPY . .

# Installer les dépendances PHP (sans les paquets de dev, pour la prod)
RUN composer install --no-dev --optimize-autoloader

EXPOSE 9000

CMD ["php-fpm"]