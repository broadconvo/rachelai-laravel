#!/bin/bash
echo "======================================================== START"
echo " Database Checkup"
echo "========================================================"

echo "Installing PostgreSQL client"
apt install -y postgresql-client

echo "Waiting for PostgreSQL to become ready..."
for i in {1..10}; do
    if pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME"; then
        echo "PostgreSQL is ready!"
        break
    else
        echo "Waiting for PostgreSQL... Attempt $i of 10"
        sleep 5
    fi
done

# Check if the loop exited due to PostgreSQL being ready or timeout
if [ $i -eq 10 ]; then
    echo "Error: PostgreSQL did not become ready after 10 attempts."
    exit 1
fi
echo "======================================================== END"
