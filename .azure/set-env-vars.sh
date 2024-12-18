#!/bin/bash

# Variables to replace with your actual values
RESOURCE_GROUP="rachelaidev_group"
APP_SERVICE_NAME="rachelai-laravel"
ENV_FILE=".env.prod"

# Check if .env file exists
if [ ! -f "$ENV_FILE" ]; then
  echo ".env file not found!"
  exit 1
fi

# Retrieve existing app settings from Azure App Service
echo "Retrieving existing app settings..."
existing_settings_json=$(az webapp config appsettings list \
  --name "$APP_SERVICE_NAME" \
  --resource-group "$RESOURCE_GROUP" \
  --query "[].{name: name, value: value}" \
  --output json)

# Check if `jq` is installed (required for JSON parsing)
if ! command -v jq &> /dev/null; then
    echo "'jq' is required but not installed. Installing jq..."
    # For macos
    brew install jq
fi

# Convert existing settings to an associative array
declare -A existing_settings
while IFS="=" read -r name value; do
  existing_settings["$name"]="$value"
done < <(echo "$existing_settings_json" | jq -r '.[] | "\(.name)=\(.value)"')

## Read each line in the .env file
while IFS='=' read -r KEY VALUE
do
  # Skip empty lines and lines starting with #
  [[ -z "$KEY" ]] && continue
  [[ "$KEY" =~ ^#.*$ ]] && continue

  # Trim whitespace
  KEY=$(echo "$KEY" | xargs)
  VALUE=$(echo "$VALUE" | xargs)

  # Remove potential quotes around the value
  VALUE="${VALUE%\"}"
  VALUE="${VALUE#\"}"
  VALUE="${VALUE%\'}"
  VALUE="${VALUE#\'}"

  # Check if key exists in existing settings
  if [[ -n "${existing_settings[$KEY]+x}" ]]; then
    # Key exists, compare values
    if [ "${existing_settings[$KEY]}" != "$VALUE" ]; then
      # Update the setting
      az webapp config appsettings set \
        --name "$APP_SERVICE_NAME" \
        --resource-group "$RESOURCE_GROUP" \
        --settings "$KEY=$VALUE" \
        --output none
      echo ">>> Updated $KEY in $APP_SERVICE_NAME"
    else
      echo "No change for $KEY"
    fi
  else
    # Key does not exist in Azure, add it
    az webapp config appsettings set \
            --name "$APP_SERVICE_NAME" \
            --resource-group "$RESOURCE_GROUP" \
            --settings "$KEY=$VALUE" \
            --output none
    echo ">>> Added $KEY in $APP_SERVICE_NAME"
  fi
done < "$ENV_FILE"

az webapp restart \
  --name "$APP_SERVICE_NAME" \
  --resource-group "$RESOURCE_GROUP"
echo "$APP_SERVICE_NAME restart complete."

echo "Environment variables update complete."
