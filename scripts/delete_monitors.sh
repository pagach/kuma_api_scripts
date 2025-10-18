#!/bin/bash
# ---------------------------------------
# Uptime Kuma - Delete all monitors safely
# Author: ChatGPT
# ---------------------------------------

# Docker container name (change if different)
CONTAINER_NAME="uptimekuma"

# Path to the database inside the container
DB_PATH="/app/data/kuma.db"

echo "🚀 Starting monitor cleanup for Uptime Kuma..."
echo "➡️  Container: $CONTAINER_NAME"
echo "➡️  Database: $DB_PATH"
echo

# Execute SQL query to delete all monitors and related data
echo "🧹 Deleting all monitors and related records..."
docker exec ${CONTAINER_NAME} sqlite3 ${DB_PATH} \
"DELETE FROM heartbeat; DELETE FROM monitor_notification; DELETE FROM monitor;"

if [ $? -eq 0 ]; then
    echo "✅ All monitors have been successfully deleted."
else
    echo "❌ An error occurred while deleting monitors!"
    exit 1
fi

# Restart Uptime Kuma to apply changes
echo "🔁 Restarting Uptime Kuma..."
docker restart ${CONTAINER_NAME}

echo
echo "🎉 Done! All monitors have been removed."
echo "---------------------------------------"
