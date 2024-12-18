echo "======================================================== START"
echo " Composer Setup"
echo "--------------------------------------------------------"
echo "- This section ensures that Composer is available and"
echo "  ready to manage PHP dependencies for your application."
echo "- Composer will be installed globally if it is not"
echo "  already available on the system."
echo "- After installation, it will handle dependency management"
echo "  in the specified working directory."
echo "--------------------------------------------------------"

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo "Composer is not installed. Installing now..."

    # Create a directory for Composer installer
    mkdir -p /home/ext

    # Download Composer installer
    wget https://getcomposer.org/installer -O /home/ext/composer-setup.php

    # Install Composer globally
    php /home/ext/composer-setup.php --install-dir=/usr/local/bin --filename=composer

    echo "Composer installed successfully."
else
    echo "Composer is already installed."
fi

# Run Composer commands to install dependencies
echo "Running Composer install..."
export COMPOSER_ALLOW_SUPERUSER=1
export COMPOSER_PROCESS_TIMEOUT=60
composer install --working-dir=/home/site/wwwroot --no-interaction --no-dev --prefer-dist

echo "--------------------------------------------------------"
echo " Composer dependencies installed successfully."
echo "========================================================"
