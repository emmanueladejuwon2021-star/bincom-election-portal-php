FROM php:8.3-apache

# Enable Apache rewrite module
RUN a2enmod rewrite

# Install MariaDB (MySQL server), client tools, and PHP MySQL extension
RUN apt-get update && apt-get install -y \
    mariadb-server \
    mariadb-client \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Optimize MariaDB memory footprint for cloud container environments
RUN mkdir -p /etc/mysql/mariadb.conf.d && \
    echo "[mysqld]\n\
innodb_buffer_pool_size = 32M\n\
key_buffer_size = 16M\n\
max_connections = 30\n\
performance_schema = OFF\n\
skip-log-bin\n" > /etc/mysql/mariadb.conf.d/99-render.cnf

# Set working directory to Apache web root
WORKDIR /var/www/html

# Copy application files into the container
COPY . /var/www/html/

# Set correct web permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
