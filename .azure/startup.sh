# name this file as "startup.sh" and call it from "startup command" as "/home/startup.sh"
# copy .azure files to /home except set-env-vars.sh
cp -r .azure/* /home
rm /home/set-env-vars.sh

bash /home/composer-setup.sh
bash /home/apt-setup.sh

echo "Copying nginx configuration"
cp /home/default /etc/nginx/sites-enabled/default

echo "Copying php.ini configuration"
cp /home/php.ini /usr/local/etc/php/conf.d/php.ini

echo "Copying laravel worker configuration"
cp /home/laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf

# restart nginx
echo "Restarting nginx"
service nginx restart
echo "Restarting supervisor"
service supervisor restart

bash /home/laravel-setup.sh
