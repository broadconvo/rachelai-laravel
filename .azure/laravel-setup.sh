echo "Setting up Laravel"

echo "Shutting down Laravel"
php /home/site/wwwroot/artisan down --refresh=15 --secret="$APP_SECRET"

echo "Copying google client"
# Transform into valid JSON using sed
fixed_json=$(echo "$GOOGLE_CREDENTIALS" | sed -E '
    s/([{,])([a-zA-Z0-9_]+):/\1"\2":/g;              # Add quotes to keys
    s/:([a-zA-Z0-9_@.\/:-]+)/:"\1"/g;               # Add quotes to string values
    s/:\[([^\]]+)\]/:[\1]/g;                        # Ignore array values for now
    s/:"\[([^]]+)\]"/:[\1]/g;                       # Handle array syntax
    s/\[([^\]]+)\]/[ "\1" ]/g;                      # Add quotes to array elements
    s/,[ ]*\]/]/g;                                  # Clean up trailing commas in arrays
')

# Write the output to a file
echo "$fixed_json" > /home/site/wwwroot/storage/app/google-api/client_secret.json

# Output to console
echo "Fixed JSON written to client_secret.json"

echo "Migrating database"
#php /home/site/wwwroot/artisan migrate --force

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
