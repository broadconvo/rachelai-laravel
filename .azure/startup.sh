#!/bin/bash
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
bash /home/site/wwwroot/.azure/nginx-setup.sh
bash /home/site/wwwroot/.azure/supervisor-setup.sh
bash /home/site/wwwroot/.azure/database-setup.sh
bash /home/site/wwwroot/.azure/laravel-setup.sh
echo ""
echo ""
echo "========================================================"
echo "INSTALL DONE"
echo "========================================================"
echo ""
zsh
