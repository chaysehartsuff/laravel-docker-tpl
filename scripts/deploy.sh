#!/bin/bash
source "/home/charts/projects/laravel-docker/scripts/tpl/tpl.sh"

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
)
declare -A FLAG_DESCRIPTIONS=(
    ["help,h"]="Displays all available parameters and flags"
    ["restart,r"]="Restarts containers"laravel-docker/scripts/deploy.sh
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

    $DOCKER_CMD up -d

    echo "Connecting containers to server network..."
    connect_to_network $APP_CONTAINER_NAME $EXTERNAL_NETWORK 
    # build manifest.json
    docker_exec "$APP_CONTAINER_NAME" "npm install" $APP_PATH
    docker_exec "$APP_CONTAINER_NAME" "npm run build" $APP_PATH
}

function restart {
    cd $SCRIPT_DIR/../

    $DOCKER_CMD restart

    echo "Connecting containers to server network..."
    connect_to_network $APP_CONTAINER_NAME $EXTERNAL_NETWORK 
    # build manifest.json
    docker_exec "$APP_CONTAINER_NAME" "npm install" $APP_PATH
    docker_exec "$APP_CONTAINER_NAME" "npm run build" $APP_PATH
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
