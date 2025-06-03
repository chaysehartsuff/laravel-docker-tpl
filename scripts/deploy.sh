#!/bin/bash
source "/root/projects/laravel-docker/scripts/tpl/tpl.sh"

# Parameter definitions
declare -A PARAMETERS=(

)
declare -A PARAMETER_DESCRIPTIONS=(

)
PARAMETER_ORDER=()

# Flag definitions
declare -A FLAGS=(
    ["help"]="show_help"
    ["h"]="show_help"
    ["restart"]=
    ["r"]=
    ["migrate"]="migrate"
    ["rebuild"]="rebuild_assets"
    ["watch"]="watch_assets"
    ["seed"]="seed"
)
declare -A FLAG_DESCRIPTIONS=(
    ["help,h"]="Displays all available parameters and flags"
    ["restart,r"]="Restarts containers"
    ["migrate"]="Runs Laravel migrations (must be deployed already)"
    ["rebuild"]="Recompiles and rebuilds js/css"
    ["watch"]="Watches for changes in js/css and rebuilds automatically"
    ["seed"]=Seeds necessary starter information for application
)

# Protection functions
declare -A PROTECTION=(
    ["assign_command"]="DOCKER_CMD,docker compose,docker-compose"
    ["source_file"]="../.env"
    ["check_command"]="docker"
)

# Command description
description="Deploys and connects service to server container."

function run {

    cd $SCRIPT_DIR/../
    APP_PATH="/var/www/html"
    echo "Current working directory: $(pwd)"

    # Start Docker containers
    $DOCKER_CMD up -d

    echo "Waiting for container '$APP_CONTAINER_NAME' to be running..."
    for i in {1..10}; do
        STATUS=$(docker inspect -f '{{.State.Running}}' "$APP_CONTAINER_NAME" 2>/dev/null)
        if [ "$STATUS" == "true" ]; then
            echo "Container is running."
            break
        fi
        echo "Still waiting... ($i/10)"
        sleep 1
    done

    # If still not running, bail early
    if [ "$STATUS" != "true" ]; then
        echo "❌ Container '$APP_CONTAINER_NAME' failed to start."
        exit 1
    fi

    echo "Connecting containers to server network..."
    connect_to_network $APP_CONTAINER_NAME $EXTERNAL_NETWORK

    # Install npm dependencies and build assets
    echo "Installing and building npm dependencies..."
    docker_exec "$APP_CONTAINER_NAME" "npm install" $APP_PATH
    docker_exec "$APP_CONTAINER_NAME" "npm run build" $APP_PATH
    docker_exec "$APP_CONTAINER_NAME" "composer install" $APP_PATH
    docker_exec "$APP_CONTAINER_NAME" "php artisan vendor:publish --all" $APP_PATH

    # Set permissions based on environment
    if [[ "$APP_ENV" == "dev" ]]; then
        echo "Setting loose permissions on local environment..."
        sudo chmod -R 777 ./src
    elif [[ "$APP_ENV" == "prod" ]]; then
        echo "Setting stricter permissions on production environment..."
        sudo chmod -R 750 ./src

        # Optimization steps for production
        echo "Optimizing application for production..."
        docker_exec "$APP_CONTAINER_NAME" "composer install --optimize-autoloader --no-dev" $APP_PATH
        docker_exec "$APP_CONTAINER_NAME" "php artisan config:clear" $APP_PATH
        docker_exec "$APP_CONTAINER_NAME" "php artisan cache:clear" $APP_PATH
        docker_exec "$APP_CONTAINER_NAME" "php artisan route:cache" $APP_PATH
        docker_exec "$APP_CONTAINER_NAME" "php artisan config:cache" $APP_PATH
        docker_exec "$APP_CONTAINER_NAME" "php artisan view:cache" $APP_PATH
    fi

    echo "Application setup complete!"
}
 
function seed {
    cd $SCRIPT_DIR/../
    APP_PATH="/var/www/html"

    SEEDERS=(
        "AdminUserSeeder"
    )

    echo "Seeding database..."
    for SEEDER in "${SEEDERS[@]}"; do
        echo "Running $SEEDER..."
        docker_exec "$APP_CONTAINER_NAME" "php artisan db:seed --class=$SEEDER" $APP_PATH
        if [ $? -ne 0 ]; then
            echo "Error: Failed to run $SEEDER"
            exit 1
        fi
    done

    echo "Database seeding completed successfully!"
    exit 0
}

function rebuild_assets {
    cd $SCRIPT_DIR/../
    APP_PATH="/var/www/html"

    echo "Rebuilding assets..."
    docker_exec "$APP_CONTAINER_NAME" "npm run build" $APP_PATH
}

function watch_assets {
    cd $SCRIPT_DIR/../
    APP_PATH="/var/www/html"

    echo "Starting watch mode for assets..."
    docker_exec "$APP_CONTAINER_NAME" "npm run watch" $APP_PATH
    exit 0
}

function migrate {
    APP_PATH="/var/www/html"
    docker_exec "$APP_CONTAINER_NAME" "php artisan migrate" $APP_PATH
    exit 0
}

function restart {
    $DOCKER_CMD down
    run
}

function docker_exec {
    local container_name="$1"
    local command="$2"
    local exec_path="$3"

    # Check if the container name is provided
    if [[ -z "$container_name" ]]; then
        color "red" "Error: Docker container name is required."
        return 1
    fi

    # Check if the command is provided
    if [[ -z "$command" ]]; then
        color "red" "Error: Command to execute is required."
        return 1
    fi

    # Default exec_path to root ("/") if not specified
    exec_path="${exec_path:-/}"

    # Execute the command using docker exec
    docker exec -it "$container_name" sh -c "cd $exec_path && $command"

    # Capture the result and provide feedback
    if [[ $? -ne 0 ]]; then
        color "red" "Error: Command execution failed in container '$container_name'."
        return 1
    else
        color "green" "Success: Command executed in container '$container_name'."
        return 0
    fi
}

function connect_to_network {
    local container_name="$1"
    local network_name="$2"

    # Check if the container is already connected to the network
    if docker network inspect "$network_name" | grep -q "\"Name\": \"$container_name\""; then
        color "yellow" "Container '$container_name' is already connected to network '$network_name'. Skipping."
    else
        color "green" "Connecting container '$container_name' to network '$network_name'..."
        docker network connect "$network_name" "$container_name"
    fi
}

main "$@"
