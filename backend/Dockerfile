FROM webdevops/php-nginx-dev:8.2

# Set working directory
WORKDIR /app

# Add application files to the image
COPY . /app

# Install Composer dependencies
RUN composer install --no-interaction

# Copy the example environment file and generate the key
COPY .env.example /app/.env
RUN chown -R www-data:www-data /app
RUN chmod 755 /app/.env # Ensure the file is writable by the script
RUN ls -la /app # Debug: List files to check presence and permissions
RUN php artisan key:generate

# Output the contents of the .env to debug (remove in production!)
RUN cat /app/.env

# Set the document root and index
ENV WEB_DOCUMENT_ROOT=/app/public
ENV WEB_DOCUMENT_INDEX=index.php
ENV XDEBUG_CLIENT_HOST=host.docker.internal
ENV PHP_DATE_TIMEZONE=Europe/Berlin

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
