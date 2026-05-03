FROM php:8.2-apache

# تثبيت المتطلبات
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# إعداد Apache
RUN a2enmod rewrite

# نسخ الملفات
COPY . /var/www/html/

# إعدادات Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# إعداد .env
RUN cp .env.example .env

WORKDIR /var/www/html

EXPOSE 8000

CMD ["sh", "-c", "composer install --no-interaction && php artisan key:generate && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000"]
