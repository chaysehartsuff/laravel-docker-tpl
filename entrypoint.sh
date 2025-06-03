#!/bin/bash

# Set file permissions
chown -R www-data:www-data /var/www/html /var/run/apache2 /var/log/apache2
chmod -R 755 /var/www/html

# Run the main container command
exec "$@"
