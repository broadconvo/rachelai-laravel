#echo "Upgrade APT (Advanced Package Tool)"
#apt upgrade

echo "Updating APT (Advanced Package Tool)"
apt-get update --allow-releaseinfo-change

echo "Install git"
apt-get install -y git
echo "Install zip unzip"
apt-get install -y zip unzip

echo "Install support for webp file conversion"
# install support for webp file conversion
apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev libwebp-dev
docker-php-ext-configure gd --with-freetype --with-webp  --with-jpeg
docker-php-ext-install gd

echo "Install supervisor"
# install support for queue
apt-get install -y supervisor
