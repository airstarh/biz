#!/bin/sh

if [ "$VA_STL_MODE" != "DEV" ]; then
    return 0
fi
cd /var/www/html || { echo "ERROR: /var/www/html does not exist"; return 0; }

mkdir -p vendor
chown -R www-data:www-data /var/www/html/vendor || true
mkdir -p node_modules
chown -R www-data:www-data /var/www/html/node_modules || true
mkdir -p /var/www/.npm
chown -R 33:33 /var/www/.npm

mkdir -p "${VA_STL_LOGS}"
EP_LOG_PATH="${VA_STL_LOGS}/entrypoint.php82.log"

echo ">>> STARTED" >> "${EP_LOG_PATH}"
composer install --no-interaction --optimize-autoloader >> "${EP_LOG_PATH}" 2>&1
echo "" >> "${EP_LOG_PATH}"
npm install >> "${EP_LOG_PATH}" 2>&1

echo "" >> "${EP_LOG_PATH}"
echo "composer install was on php 8.2" >> "${EP_LOG_PATH}"
date +"%Y-%m-%d %H:%M:%S" >> "${EP_LOG_PATH}"
echo "" >> "${EP_LOG_PATH}"
echo "" >> "${EP_LOG_PATH}"
echo "" >> "${EP_LOG_PATH}"

# region initial

# Create required directories if they don't exist
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/bootstrap/cache

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Generate application key if it doesn't exist
if [ -z "$(grep '^APP_KEY=' .env | grep -v '=$')" ]; then
    php artisan key:generate
fi

# Run migrations if the database is ready
if [ "$DB_HOST" != "" ]; then
    # Wait for the database to be ready
    until nc -z -v -w30 $DB_HOST 3306; do
      echo "Waiting for database connection..."
      # Wait for 5 seconds before check again
      sleep 5
    done

    php artisan migrate --force
fi

# endregion initial


