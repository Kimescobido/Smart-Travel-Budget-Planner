FROM php:8.2-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache variables pass-through
RUN echo "PassEnv DB_HOST DB_PORT DB_NAME DB_USER DB_PASS OPENWEATHER_API_KEY EXCHANGE_RATE_API_KEY UNSPLASH_ACCESS_KEY RESEND_API_KEY" >> /etc/apache2/apache2.conf

# Copy project files
COPY . /var/www/html/

# Set proper permissions so www-data can read all files
RUN chown -R www-data:www-data /var/www/html/ && chmod -R 755 /var/www/html/

EXPOSE 80
CMD ["apache2-foreground"]