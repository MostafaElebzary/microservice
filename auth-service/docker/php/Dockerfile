FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd sockets

WORKDIR /var/www

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY src/ .

RUN composer install

RUN chown -R www-data:www-data /var/www/storage
RUN chmod -R 775 /var/www/storage

EXPOSE 9000
CMD ["php-fpm"]