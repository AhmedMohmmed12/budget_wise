# Use official PHP 8.3 Apache image
FROM php:8.3-apache

# -------------------------
# 1. Install system dependencies
# -------------------------
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# -------------------------
# 2. Enable Apache rewrite & allow .htaccess
# -------------------------
RUN a2enmod rewrite
# Allow .htaccess and set ServerName
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# -------------------------
# 3. Install PHP extensions required by Laravel
# -------------------------
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd

# -------------------------
# 4. Set Apache Document Root to Laravel public
# -------------------------
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# -------------------------
# 5. Install Composer
# -------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# -------------------------
# 6. Set working directory to Laravel root
# -------------------------
WORKDIR /var/www/html

# -------------------------
# 7. Copy application code
# -------------------------
COPY . .

# -------------------------
# 8. Install PHP dependencies
# -------------------------
RUN composer install --optimize-autoloader --no-dev

# -------------------------
# 9. Install Node dependencies and build frontend
# -------------------------
RUN npm ci && npm run build

# -------------------------
# 10. Set proper permissions for Laravel
# -------------------------
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public

# -------------------------
# 11. Expose port 80
# -------------------------
EXPOSE 80

# -------------------------
# 12. Start Apache in foreground
# -------------------------
CMD ["apache2-foreground"]
