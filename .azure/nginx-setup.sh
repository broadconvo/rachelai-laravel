#!/bin/bash

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
