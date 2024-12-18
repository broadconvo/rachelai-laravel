echo "========================================================"
echo " SCRIPT: Laravel Deployment and Setup Script"
echo "--------------------------------------------------------"
echo " DESCRIPTION:"
echo " This script automates essential steps for setting up"
echo " and deploying a Laravel application on a server. It"
echo " includes tasks such as updating environment configurations,"
echo " clearing and caching Laravel components, handling Google"
echo " API credentials, and restarting Laravel workers."
echo ""
echo " FEATURES:"
echo " - Shuts down Laravel during critical operations."
echo " - Copies and configures Google API credentials."
echo " - Clears and caches routes, configs, and views."
echo " - Links storage directories for Laravel."
echo " - Optionally handles database migrations and asset building."
echo ""
echo " USAGE:"
echo " - Ensure necessary environment variables like \$APP_SECRET"
echo "   and \$GOOGLE_CREDENTIALS are set before running the script."
echo " - Run the script with appropriate permissions:"
echo "     chmod +x script.sh"
echo "     ./script.sh"
echo ""
echo " NOTES:"
echo " - Uncomment the database migration and asset building"
echo "   sections if needed."
echo " - Adjust file paths and variables as required for your setup."
echo ""
echo " AUTHOR: Ern Gregorio"
echo " VERSION: 1.0"
echo " DATE: 2024-12-18"
echo "========================================================"

echo "Shutting down Laravel"
php /home/site/wwwroot/artisan down --refresh=15 --secret="$APP_SECRET"

echo "Copying google client"
# Path to the file
FILE="/home/site/wwwroot/storage/app/google-api/client_secret.json"

# Check if the file exists
if [ -f "$FILE" ]; then
    echo "File $FILE already exists."
else
    # Transform into valid JSON using sed
    fixed_json=$(echo "$GOOGLE_CREDENTIALS" | sed -E '
        s/([{,])([a-zA-Z0-9_]+):/\1"\2":/g;             # Add quotes to keys
        s/:([a-zA-Z0-9_@.\/:-]+)/:"\1"/g;              # Add quotes to string values
        s/:\"([a-zA-Z0-9_@.\/:-]+)\"/\:"\1"/g;         # Ensure values with special characters stay quoted
        s/:\[([^\]]+)\]/:[\1]/g;                       # Ignore array values
        s/"\[([^]]+)\]"/:[\1]/g;                       # Handle array syntax
        s/\[([^\]]+)\]/[ "\1" ]/g;                     # Add quotes to array elements
        s/,[ ]*\]/]/g;                                 # Clean up trailing commas in arrays
    ')

    echo "File $FILE does not exist. Creating it..."
    # Create the file and add default content if needed
    mkdir -p "$(dirname "$FILE")"  # Ensure the parent directory exists
    touch "$FILE"                 # Create the file
    echo "{}" > "$FILE"           # Add default JSON content (optional)
    echo "File $FILE has been created."

    # Write the output to a file
    echo "$fixed_json" > $FILE

    # Output to console
    echo "Fixed JSON written to $FILE"
fi


#echo "Migrating database"
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
