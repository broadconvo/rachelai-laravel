# name this file as "startup.sh" and call it from "startup command" as "/home/startup.sh"
cp /home/site/wwwroot/.azure/* /home/
rm -rf /home/set-env-vars.sh

bash /home/site/wwwroot/.azure/apt-setup.sh
bash /home/site/wwwroot/.azure/zsh-setup.sh
bash /home/site/wwwroot/.azure/composer-setup.sh

echo "======================================================== START"
echo " Additional Setups"
echo "========================================================"

echo "Copying nginx configuration"
cp /home/site/wwwroot/.azure/nginx-default /etc/nginx/sites-enabled/default

echo "Copying php.ini configuration"
cp /home/site/wwwroot/.azure/php.ini /usr/local/etc/php/conf.d/php.ini

echo "Copying laravel worker configuration"
cp /home/site/wwwroot/.azure/laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf

echo "Creating supervisor Directory"
mkdir "/home/site/wwwroot/storage/app/logs/supervisor"

echo "Restarting nginx"
service nginx stop
service nginx start

echo "Restarting supervisor"
service supervisor stop
service supervisor start
echo "======================================================== END"

bash /home/site/wwwroot/.azure/database-setup.sh
bash /home/site/wwwroot/.azure/laravel-setup.sh

zsh
