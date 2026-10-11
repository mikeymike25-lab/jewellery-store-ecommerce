#!/bin/bash

# Simplified Laravel startup script for Docker Compose
echo "Starting Laravel application..."

# Set default PORT if not provided
export PORT=${PORT:-80}

# Create .env file with environment variables from Docker Compose
# Ensure APP_KEY exists before writing .env
if [ -z "$APP_KEY" ]; then
    echo "No APP_KEY passed in environment, generating one..."
    export APP_KEY="base64:$(openssl rand -base64 32)"
else
    echo "Using existing APP_KEY from environment."
fi

# Normalize APP_URL with https:// prefix if needed
if [ -n "$APP_URL" ] && [[ "$APP_URL" != http* ]]; then
    APP_URL="https://$APP_URL"
fi

# Create .env file with safely quoted environment variables
echo "Creating .env file..."
cat > .env << EOF
APP_NAME="${APP_NAME:-Eclore}"
APP_ENV=${APP_ENV:-production}
APP_DEBUG=${APP_DEBUG:-false}
APP_KEY="${APP_KEY}"
APP_URL="${APP_URL:-http://localhost:$PORT}"

LOG_CHANNEL=stack
LOG_LEVEL=${LOG_LEVEL:-error}

DB_CONNECTION=${DB_CONNECTION:-pgsql}
DB_HOST="${DB_HOST:-}"
DB_PORT=${DB_PORT:-5432}
DB_DATABASE="${DB_DATABASE:-}"
DB_USERNAME="${DB_USERNAME:-}"
DB_PASSWORD="${DB_PASSWORD:-}"
DATABASE_URL="${DATABASE_URL:-}"
DB_URL="${DATABASE_URL:-}"

BROADCAST_DRIVER=log
CACHE_DRIVER=${CACHE_DRIVER:-file}
CACHE_STORE=${CACHE_STORE:-file}
FILESYSTEM_DISK=local
QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}
SESSION_DRIVER=${SESSION_DRIVER:-file}
SESSION_LIFETIME=120

REDIS_HOST="${REDIS_HOST:-}"
REDIS_PASSWORD="${REDIS_PASSWORD:-}"
REDIS_PORT=${REDIS_PORT:-6379}

MAIL_MAILER=${MAIL_MAILER:-smtp}
MAIL_HOST="${MAIL_HOST:-}"
MAIL_PORT=${MAIL_PORT:-587}
MAIL_USERNAME="${MAIL_USERNAME:-}"
MAIL_PASSWORD="${MAIL_PASSWORD:-}"
MAIL_ENCRYPTION="${MAIL_ENCRYPTION:-tls}"
MAIL_FROM_ADDRESS="${MAIL_FROM_ADDRESS:-info@eclorejewellery.com}"
MAIL_FROM_NAME="Eclore"

XENDIT_SECRET_KEY="${XENDIT_SECRET_KEY:-}"
XENDIT_PUBLIC_KEY="${XENDIT_PUBLIC_KEY:-}"
XENDIT_WEBHOOK_VERIFICATION_TOKEN="${XENDIT_WEBHOOK_VERIFICATION_TOKEN:-}"

VITE_APP_NAME="Eclore"
EOF

# Clear Laravel config cache
echo "Clearing Laravel config cache..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Create necessary directories
echo "Creating directories..."
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/app/public
mkdir -p bootstrap/cache

# Set permissions
chmod -R 775 storage bootstrap/cache
php artisan storage:link || true

# Wait for database to be ready
echo "Waiting for database connection..."
timeout=60
counter=0
until php artisan tinker --execute="DB::connection()->getPdo();" 2>/dev/null; do
    echo "Database is unavailable - sleeping ($counter/$timeout)"
    sleep 2
    counter=$((counter + 2))
    if [ $counter -ge $timeout ]; then
        echo "Database connection timeout - continuing anyway"
        break
    fi
done

if [ $counter -lt $timeout ]; then
    echo "Database is ready!"
    echo "Running migrations..."
    php artisan migrate --force || echo "Migrations completed with notices, continuing..."
else
    echo "Skipping migrations due to database connection timeout"
fi

# Test Laravel configuration
echo "Testing Laravel configuration..."
php artisan config:show app.name
php artisan config:show app.key

# Configure Apache for the correct port
echo "Configuring Apache for port $PORT..."
echo "ServerName localhost" >> /etc/apache2/apache2.conf
echo "Listen $PORT" > /etc/apache2/ports.conf
sed -i -E "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf
a2ensite 000-default.conf

# Test Apache configuration
echo "Testing Apache configuration..."
apache2ctl configtest

# Start Apache directly in foreground as main container process
echo "Starting Apache on port $PORT in foreground..."
echo "Server is available at: http://0.0.0.0:$PORT"
echo "Health endpoint: http://0.0.0.0:$PORT/health"
exec apache2ctl -D FOREGROUND