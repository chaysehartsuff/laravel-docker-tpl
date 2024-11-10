# Use the official PHP 8.2 image with Apache pre-installed
FROM php:8.2-apache

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim unzip git curl \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif bcmath gd \
    && rm -rf /var/lib/apt/lists/*

# Install Composer and Laravel Installer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer global require laravel/installer

# Enable Apache modules for Laravel
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Add entrypoint script to set file permissions at runtime
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port 8000
EXPOSE 8000

# Set entrypoint to handle file permissions at runtime
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Start Apache server
CMD ["apache2-foreground"]
