FROM php:8.2-apache

RUN apt-get update && apt-get install -y unzip git \
    && docker-php-ext-install pdo pdo_mysql

# устанавливаем composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY ./www /var/www/html

RUN composer install

# используем стандартную команду apache
CMD ["apache2-foreground"]