# name this file as "startup.sh" and call it from "startup command" as "/home/startup.sh"
bash /home/site/wwwroot/.azure/zsh-setup.sh
bash /home/site/wwwroot/.azure/apt-setup.sh
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

echo "Restarting nginx"
service nginx restart

echo "Restarting supervisor"
service supervisor restart
echo "======================================================== END"

bash /home/site/wwwroot/.azure/laravel-setup.sh

zsh
