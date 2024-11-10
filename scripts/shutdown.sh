#!/bin/bash

# Ensure the script works relative to its location
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR/../" || exit

# Default action: bring down Docker Compose
docker compose down

# Parse flags for additional cleanup actions
for arg in "$@"; do
  case $arg in
    --remove-containers)
      echo "Removing all stopped containers..."
      docker container prune -f
      ;;
    --remove-images)
      echo "Removing all unused images..."
      docker image prune -a -f
      ;;
    --remove-volumes)
      echo "Removing all unused volumes..."
      docker volume prune -f
      ;;
    --remove-networks)
      echo "Removing all unused networks..."
      docker network prune -f
      ;;
    --remove-all)
      echo "Stopping all running containers and removing all containers, images, volumes, and networks..."
      docker system prune -a --volumes -f
      ;;
    --clear-cache)
      echo "Clearing Docker build cache..."
      docker builder prune -f
      ;;
    *)
      echo "Unknown option: $arg"
      echo "Usage: $0 [--remove-containers] [--remove-images] [--remove-volumes] [--remove-networks] [--remove-all] [--clear-cache]"
      exit 1
      ;;
  esac
done
