#!/bin/bash
# ---------------------------------------
# Uptime Kuma - Add notification to all monitors
# Author: ChatGPT
# ---------------------------------------

# Dobavi direktorij u kojem se nalazi bash skripta
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Pokreni PHP skriptu koristeći apsolutni put
php "${SCRIPT_DIR}/../api/create_monitors.php"

# Docker container name (change if different)
CONTAINER_NAME="uptimekuma"

# Path to the database inside the container
DB_PATH="/app/data/kuma.db"

# Notification ID to assign to all monitors
NOTIFICATION_ID=1

echo "Starting process to assign notification ID $NOTIFICATION_ID to all monitors..."
echo "Container: $CONTAINER_NAME"
echo "Database: $DB_PATH"
echo

# 1. Add the notification to all monitors
echo "Adding notification ID $NOTIFICATION_ID to all monitors..."
docker exec ${CONTAINER_NAME} sqlite3 ${DB_PATH} "INSERT OR IGNORE INTO monitor_notification (monitor_id, notification_id) SELECT id, ${NOTIFICATION_ID} FROM monitor;"

if [ $? -eq 0 ]; then
    echo "Notification successfully assigned to all monitors."
else
    echo "Error: Failed to assign notification to monitors."
    exit 1
fi

# 2. Restart Uptime Kuma to apply changes
echo "Restarting Uptime Kuma..."
docker restart ${CONTAINER_NAME}

echo
echo "All done. Notification ID ${NOTIFICATION_ID} has been added to all monitors."
echo "---------------------------------------"
