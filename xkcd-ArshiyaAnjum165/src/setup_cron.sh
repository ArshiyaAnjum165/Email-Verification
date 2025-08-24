#!/bin/bash

CRON_FILE="mycronjob"
CRON_JOB="*/5 * * * * php $(pwd)/cron.php"

# Get existing cron jobs
crontab -l 2>/dev/null > $CRON_FILE

# Remove any previous cron.php entries
grep -v "cron.php" $CRON_FILE > temp && mv temp $CRON_FILE

# Add our new CRON job
echo "$CRON_JOB" >> $CRON_FILE

# Apply CRON job
crontab $CRON_FILE
rm $CRON_FILE

echo "CRON job installed to run every 5 minutes."
