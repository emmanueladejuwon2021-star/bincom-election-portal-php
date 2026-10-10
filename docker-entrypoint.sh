#!/bin/sh
set -e

PORT="${PORT:-80}"

# Adjust Apache configuration to listen on the dynamic Render port
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# If using local MySQL (default behavior)
if [ -z "$DB_HOST" ] || [ "$DB_HOST" = "127.0.0.1" ] || [ "$DB_HOST" = "localhost" ]; then
    echo "Starting internal MySQL service..."
    service mariadb start || service mysql start

    # Wait for MySQL socket to become available
    for i in $(seq 1 30); do
        if mysqladmin ping -u root --silent 2>/dev/null; then
            break
        fi
        sleep 1
    done

    # Ensure database exists
    mysql -u root -e "CREATE DATABASE IF NOT EXISTS bincom_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

    # Verify if election tables exist
    TABLE_COUNT=$(mysql -u root -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'bincom_test';" -s -N 2>/dev/null || echo 0)
    if [ "$TABLE_COUNT" -eq 0 ]; then
        echo "Auto-importing Delta State election data from bincom_test.sql into MySQL..."
        mysql -u root bincom_test < /var/www/html/bincom_test.sql
        echo "MySQL import completed successfully."
    else
        echo "MySQL database bincom_test already contains ${TABLE_COUNT} tables."
    fi
fi

echo "Starting Apache web server on port ${PORT}..."
exec apache2-foreground
