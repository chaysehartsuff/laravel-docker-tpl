#!/bin/bash

# Ensure the script works relative to its location
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Check if the src directory exists
SRC_DIR="$SCRIPT_DIR/../src"
if [ -d "$SRC_DIR" ]; then
  # Check if the src directory contains any files or directories
  if [ "$(ls -A "$SRC_DIR")" ]; then
    echo "Error: The src directory already exists and is not empty. Please empty or remove it before running this script."
    exit 1
  fi
else
  echo "Error: The src directory does not exist. Please create it before running this script."
  exit 1
fi

# Ensure a project name (parameter 1) is provided
if [ -z "$1" ]; then
  echo "Error: You must provide a project name."
  echo "Usage: ./setup.sh <project-name>"
  exit 1
fi

# Store the project name from the first argument
PROJECT_NAME=$1

# Bring up Docker containers in detached mode
cd "$SCRIPT_DIR/../" && docker compose up -d

# Check if the volume is correctly mounted and writable inside the container
docker compose exec -it src bash -c "touch /var/www/html/testfile || { echo 'Error: Cannot write to /var/www/html. Check permissions and volume mounting.'; exit 1; }"

# Clean up testfile after the check
docker compose exec -it src bash -c "rm -f /var/www/html/testfile"

# Use Composer to create a new Laravel project interactively
docker compose exec -it src bash -c "cd /var/www/html && /root/.composer/vendor/bin/laravel new $PROJECT_NAME"

# MOVE CONTENTS FROM /var/www/html/$PROJECT_NAME to /var/www/html
docker compose exec -it src bash -c "mv /var/www/html/$PROJECT_NAME/* /var/www/html/ && mv /var/www/html/$PROJECT_NAME/.* /var/www/html/ 2>/dev/null || true"

# Remove empty project dir
docker compose exec -it src bash -c "rm -rf /var/www/html/$PROJECT_NAME"

# Read DB vars from ../.env and overwrite Laravel's .env in the container
DB_VARS=("DB_HOST" "DB_PORT" "DB_DATABASE" "DB_USERNAME" "DB_PASSWORD")

for VAR in "${DB_VARS[@]}"; do
  VALUE=$(grep "^$VAR=" "$SCRIPT_DIR/../.env" | cut -d '=' -f2-)
  if [ -n "$VALUE" ]; then
    docker compose exec -T src bash -c "sed -i 's|^$VAR=.*|$VAR=$VALUE|' /var/www/html/.env"
  fi
done

# Run "php artisan migrate:fresh" in the src container at /var/www/html
docker compose exec -it src bash -c "cd /var/www/html && php artisan migrate:fresh"

# Take down the Docker containers
docker compose down

# Change ownership of ./src to allow modification by the current user
sudo chown -R "$(whoami)":"$(whoami)" "$SRC_DIR"

# Provide success message after the user completes the prompt
echo "Laravel project '$PROJECT_NAME' created successfully inside the container."
