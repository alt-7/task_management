# Базовый образ с PHP 8.3 CLI
FROM php:8.3-cli-alpine

# Устанавливаем зависимости
RUN apk add --no-cache \
    bash \
    git \
    unzip \
    curl \
    libpq \
    postgresql-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql xml

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Устанавливаем рабочую директорию
WORKDIR /var/www/app

# Копируем composer.json и composer.lock для кеша
COPY composer.json composer.lock ./

# Устанавливаем зависимости Symfony
RUN composer install --no-scripts --no-autoloader || true

# Копируем весь проект
COPY . ./

# Дозаливаем автолоадер
RUN composer dump-autoload --optimize

# Открываем порт 8000
EXPOSE 8000

# Команда запуска
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
