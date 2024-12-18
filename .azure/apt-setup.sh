#echo "Upgrade APT (Advanced Package Tool)"
#apt upgrade

# --------------------------------------------------------------------
echo "Updating APT (Advanced Package Tool)"
apt-get update --allow-releaseinfo-change

# --------------------------------------------------------------------
if ! command -v git &> /dev/null; then
    echo "Git is not installed. Installing Git..."
    apt-get install -y git
    git config --global --add safe.directory /home/site/wwwroot
else
    echo "Git is already installed."
fi

# --------------------------------------------------------------------
# Check and install Zip/Unzip
if ! command -v zip &> /dev/null || ! command -v unzip &> /dev/null; then
    echo "Zip or Unzip is not installed. Installing Zip and Unzip..."
    apt-get install -y zip unzip
else
    echo "Zip and Unzip are already installed."
fi

# --------------------------------------------------------------------
# Check and install support for WebP file conversion
echo "Checking and installing support for WebP file conversion..."
if ! dpkg -l | grep -qE 'libfreetype6-dev|libjpeg62-turbo-dev|libpng-dev|libwebp-dev'; then
    apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev libwebp-dev
else
    echo "WebP support libraries are already installed."
fi

# --------------------------------------------------------------------
# Configure and install GD extension
if ! php -m | grep -q gd; then
    echo "GD extension is not installed. Configuring and installing GD..."
    docker-php-ext-configure gd --with-freetype --with-webp --with-jpeg
    docker-php-ext-install gd
else
    echo "GD extension is already installed."
fi

# --------------------------------------------------------------------
if ! command -v supervisorctl &> /dev/null; then
    echo "Supervisor is not installed. Installing Supervisor..."
    apt-get install -y supervisor
else
    echo "Supervisor is already installed."
fi
