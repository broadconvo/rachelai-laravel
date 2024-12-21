echo "========================================================"
echo "STARTUP"
echo "========================================================"
echo "Starting startup script..."
echo "Create directory for installation scripts"
mkdir /home/install
echo "Copying installation scripts"
cp /home/site/wwwroot/.azure/* /home/install/
rm -rf /home/install/set-env-vars.sh

bash /home/site/wwwroot/.azure/apt-setup.sh
bash /home/site/wwwroot/.azure/zsh-setup.sh
bash /home/site/wwwroot/.azure/composer-setup.sh

echo "========================================================"
echo "Nginx Setup"
echo "--------------------------------------------------------"

echo "--- Copying nginx configuration"
cp /home/site/wwwroot/.azure/nginx-default /etc/nginx/sites-enabled/default

echo "--- Copying php.ini configuration"
cp /home/site/wwwroot/.azure/php.ini /usr/local/etc/php/conf.d/php.ini

echo "--- Restarting nginx"
service nginx stop
service nginx start
echo "--------------------------------------------------------"
echo "END"
echo "========================================================"
echo ""

echo "========================================================"
echo "Supervisor Setup"
echo "--------------------------------------------------------"

echo "Copying laravel worker supervisor configuration"
cp /home/site/wwwroot/.azure/laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf

echo "Creating supervisor Directory"
mkdir "/home/site/wwwroot/storage/logs/supervisor"
touch "/home/site/wwwroot/storage/logs/supervisor/queue_worker.err.log"
touch "/home/site/wwwroot/storage/logs/supervisor/queue_worker.out.log"
touch "/home/site/wwwroot/storage/logs/supervisor/gmail_process_messages.err.log"
touch "/home/site/wwwroot/storage/logs/supervisor/gmail_process_messages.out.log"

echo "Restarting supervisor"
service supervisor stop
service supervisor start

echo "Waiting for Supervisor to start..."
for i in {1..10}; do
    if service supervisor status | grep -q "is running"; then
        echo "Supervisor has started successfully!"
        break
    fi
    echo "Supervisor is not running yet. Retrying in 2 seconds... ($i/10)"
    sleep 2
done

if ! service supervisor status | grep -q "is running"; then
    echo "Error: Supervisor did not start after 10 attempts."
    exit 1
fi
echo "--------------------------------------------------------"
echo "END"
echo "========================================================"
echo ""

bash /home/site/wwwroot/.azure/database-setup.sh
bash /home/site/wwwroot/.azure/laravel-setup.sh

echo ""
echo ""
echo "========================================================"
echo "INSTALL DONE"
echo "========================================================"
echo ""
zsh
