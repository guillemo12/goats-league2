FROM php:8.2-fpm-alpine

# Install nginx and php extensions needed
RUN apk add --no-cache nginx supervisor \
    && docker-php-ext-install pdo pdo_mysql

# Copy nginx config
COPY nginx.conf /etc/nginx/nginx.conf

# Copy project files
COPY main/ /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Create uploads dir and set permissions
RUN mkdir -p /var/www/html/uploads/profiles \
    && chown -R www-data:www-data /var/www/html/uploads

# Copy supervisor config to run nginx + php-fpm together
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
