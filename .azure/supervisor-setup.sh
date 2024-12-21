#!/bin/bash
echo "========================================================"
echo "Supervisor Setup"
echo "--------------------------------------------------------"

echo "Copying laravel worker supervisor configuration"
cp /home/site/wwwroot/.azure/laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf

echo "Creating supervisor Directory"
mkdir "/home/site/wwwroot/storage/logs/supervisor"
touch "/home/site/wwwroot/storage/logs/supervisor/queue_worker.err.log"
touch "/home/site/wwwroot/storage/logs/supervisor/queue_worker.out.log"
touch "/home/site/wwwroot/storage/logs/supervisor/gmail_process_messages.err.log"
touch "/home/site/wwwroot/storage/logs/supervisor/gmail_process_messages.out.log"

echo "Restarting supervisor"
service supervisor stop
service supervisor start

echo "Waiting for Supervisor to start..."
for i in {1..10}; do
    if service supervisor status | grep -q "is running"; then
        echo "Supervisor has started successfully!"
        break
    fi
    echo "Supervisor is not running yet. Retrying in 2 seconds... ($i/10)"
    sleep 2
done

if ! service supervisor status | grep -q "is running"; then
    echo "Error: Supervisor did not start after 10 attempts."
    exit 1
fi
echo "--------------------------------------------------------"
echo "END"
echo "========================================================"
echo ""
