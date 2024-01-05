#!/bin/bash
echo "Replacing nginx configuration for serving PHP 8.x-based applications"
cp /home/site/wwwroot/deployment/azure-app-service/nginx.conf /etc/nginx/sites-available/default

echo "Reloading nginx to apply new configuration"
service nginx reload
