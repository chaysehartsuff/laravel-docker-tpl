#!/bin/bash

# Ensure the script works relative to its location
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR/../" || exit

# Load environment variables from .env
set -a
source .env
set +a

# Default action: Bring up Docker Compose in detached mode
docker compose up -d

# Parse flags for additional deployment actions
for arg in "$@"; do
  case $arg in
    --build)
      echo "Building Docker images..."
      docker compose build
      ;;
    --build-no-cache)
      echo "Building Docker images without cache..."
      docker compose build --no-cache
      ;;
    --pull)
      echo "Pulling the latest images..."
      docker compose pull
      ;;
    --up)
      echo "Starting Docker Compose in detached mode..."
      docker compose up -d
      ;;
    --up-recreate)
      echo "Starting Docker Compose with forced container recreation..."
      docker compose up -d --force-recreate
      ;;
    --up-build)
      echo "Building and starting Docker Compose..."
      docker compose up -d --build
      ;;
    --logs)
      echo "Tailing logs for Docker Compose services..."
      docker compose logs -f
      ;;
    *)
      echo "Unknown option: $arg"
      echo "Usage: $0 [--build] [--build-no-cache] [--pull] [--up] [--up-recreate] [--up-build] [--logs]"
      exit 1
      ;;
  esac
done

# Connect the application container to the specified external network
if [ -n "$EXTERNAL_NETWORK" ] && [ -n "$APP_CONTAINER_NAME" ]; then
  echo "Connecting $APP_CONTAINER_NAME to $EXTERNAL_NETWORK..."
  docker network connect "$EXTERNAL_NETWORK" "$APP_CONTAINER_NAME"
else
  echo "Error: EXTERNAL_NETWORK or APP_CONTAINER_NAME is not set in .env"
  exit 1
fi
