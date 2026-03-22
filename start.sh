#!/bin/sh
# Substitute ${PORT} into nginx config using the Railway-provided PORT variable
# Default to 8080 if PORT is not set
export PORT="${PORT:-8080}"
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

# Start supervisor (which starts both php-fpm and nginx)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
