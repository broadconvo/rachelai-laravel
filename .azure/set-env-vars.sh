#!/bin/bash

# Variables to replace with your actual values
RESOURCE_GROUP="rachelai-laravel-group"
APP_SERVICE_NAME="rachelai-laravel"
ENV_FILE="../.env"

# Check if .env file exists
if [ ! -f "$ENV_FILE" ]; then
  echo ".env file not found!"
  exit 1
fi

# Read each line in the .env file
while IFS='=' read -r KEY VALUE
do
  # Skip empty lines and lines starting with #
  [[ "$KEY" =~ ^#.*$ ]] && continue
  [[ -z "$KEY" ]] && continue

  # Remove potential quotes around the value
  VALUE="${VALUE%\"}"
  VALUE="${VALUE#\"}"
  VALUE="${VALUE%\'}"
  VALUE="${VALUE#\'}"

  # Set the environment variable in Azure App Service
  az webapp config appsettings set \
    --name "$APP_SERVICE_NAME" \
    --resource-group "$RESOURCE_GROUP" \
    --settings "$KEY=$VALUE" \
    --output none

  echo "Set $KEY"
done < "$ENV_FILE"

echo "All environment variables have been set."
