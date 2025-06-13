FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u 1000 -d /home/user user
RUN mkdir -p /home/user/.composer && \
    chown -R user:user /home/user

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=user:user . /var/www

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Create required directories and set permissions
RUN mkdir -p /var/www/storage/framework/{cache,sessions,views} \
    /var/www/storage/logs \
    /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage \
    && chown -R www-data:www-data /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

# Copy nginx configuration
COPY docker/nginx/default.conf /etc/nginx/sites-available/default

# Copy supervisor configuration
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy php-fpm configuration
RUN echo "[www]" > /etc/php/8.2/fpm/pool.d/www.conf && \
    echo "user = www-data" >> /etc/php/8.2/fpm/pool.d/www.conf && \
    echo "group = www-data" >> /etc/php/8.2/fpm/pool.d/www.conf && \
    echo "listen = 127.0.0.1:9000" >> /etc/php/8.2/fpm/pool.d/www.conf && \
    echo "pm = dynamic" >> /etc/php/8.2/fpm/pool.d/www.conf && \
    echo "pm.max_children = 50" >> /etc/php/8.2/fpm/pool.d/www.conf && \
    echo "pm.start_servers = 5" >> /etc/php/8.2/fpm/pool.d/www.conf && \
    echo "pm.min_spare_servers = 5" >> /etc/php/8.2/fpm/pool.d/www.conf && \
    echo "pm.max_spare_servers = 35" >> /etc/php/8.2/fpm/pool.d/www.conf

# Create start script
RUN echo '#!/bin/bash' > /start.sh && \
    echo 'php artisan config:cache' >> /start.sh && \
    echo 'php artisan route:cache' >> /start.sh && \
    echo 'php artisan view:cache' >> /start.sh && \
    echo 'supervisord -c /etc/supervisor/conf.d/supervisord.conf' >> /start.sh && \
    chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]