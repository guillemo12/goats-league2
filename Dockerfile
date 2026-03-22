FROM php:8.2-fpm-alpine

# Install nginx, supervisor, and needed tools
RUN apk add --no-cache nginx supervisor gettext \
    && docker-php-ext-install pdo pdo_mysql

# Copy nginx config TEMPLATE (uses ${PORT})
COPY nginx.conf /etc/nginx/nginx.conf.template

# Copy project files into web root
COPY main/ /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && mkdir -p /var/www/html/uploads/profiles \
    && chown -R www-data:www-data /var/www/html/uploads

# Copy supervisor config
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Startup script: substitute PORT variable into nginx config then start services
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080

CMD ["/start.sh"]
