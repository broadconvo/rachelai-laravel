

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
composer install --working-dir=/home/site/wwwroot --no-interaction --no-dev --prefer-dist

echo "Composer dependencies installed successfully."
