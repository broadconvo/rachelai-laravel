echo "Setting up Laravel"

echo "Shutting down Laravel"
php /home/site/wwwroot/artisan down --refresh=15 --secret="$APP_SECRET"

echo "Migrating database"
php /home/site/wwwroot/artisan migrate --force

echo "Clearing cache"
php /home/site/wwwroot/artisan cache:clear

#echo "Clearing expired password reset tokens"
#php /home/site/wwwroot/artisan auth:clear-resets

echo "Clearing and caching routes"
php /home/site/wwwroot/artisan route:cache

echo "Clearing and caching config"
php /home/site/wwwroot/artisan config:cache

echo "Clearing and caching views"
php /home/site/wwwroot/artisan view:cache

# Install node modules
#echo "Installing node modules"
# npm ci

# Build assets using Laravel Mix
#echo "Building assets"
# npm run production --silent

echo "Linking storage"
php /home/site/wwwroot/artisan storage:link

echo "Turning on Laravel"
php /home/site/wwwroot/artisan up

echo "Running Laravel worker"
nohup php /home/site/wwwroot/artisan queue:work &
