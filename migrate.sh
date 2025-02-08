#!/bin/bash
# full_migrate.sh - A script to drop the database, recreate it, clear cache, sync metadata,
# delete previous migrations, generate a new migration, and apply it.
#
# Usage:
#   ./full_migrate.sh
#
# Ensure this script is executable:
#   chmod +x full_migrate.sh

# Step 0: Clear Symfony cache.
echo "Clearing cache..."
php bin/console cache:clear --no-warmup
if [ $? -ne 0 ]; then
    echo "Error: Cache clearing failed. Please check the output above."
    exit 1
fi

# Step 1: Drop the database (if exists).
echo "Dropping database..."
php bin/console doctrine:database:drop --force --if-exists
if [ $? -ne 0 ]; then
    echo "Error: Database drop failed. Please check the output above."
    exit 1
fi

# Step 2: Create a new database.
echo "Creating database..."
php bin/console doctrine:database:create
if [ $? -ne 0 ]; then
    echo "Error: Database creation failed. Please check the output above."
    exit 1
fi

# Step 3: Synchronize metadata storage (first run).
echo "Synchronizing metadata storage (1st run)..."
php bin/console doctrine:migrations:sync-metadata-storage --no-interaction
if [ $? -ne 0 ]; then
    echo "Error: First metadata storage synchronization failed. Please check the output above."
    exit 1
fi

# Step 4: Optional pause and second sync.
echo "Waiting 2 seconds before re-syncing metadata storage..."
sleep 2
echo "Synchronizing metadata storage (2nd run)..."
php bin/console doctrine:migrations:sync-metadata-storage --no-interaction
if [ $? -ne 0 ]; then
    echo "Error: Second metadata storage synchronization failed. Please check the output above."
    exit 1
fi

# Step 5: Delete all previous migration files.
# Adjust the path if your migrations directory is different.
echo "Deleting previous migration files..."
rm -rf migrations/*.php
if [ $? -ne 0 ]; then
    echo "Error: Failed to delete previous migration files. Please check the output above."
    exit 1
fi

# Step 6: Generate the migration file.
echo "Generating migration file..."
php bin/console make:migration
if [ $? -ne 0 ]; then
    echo "Error: Migration generation failed. Please check the output above."
    exit 1
fi

# Step 7: Apply the migrations.
echo "Applying migrations..."
php bin/console doctrine:migrations:migrate --no-interaction
if [ $? -ne 0 ]; then
    echo "Error: Migration application failed. Please check the output above."
    exit 1
fi

echo "Database dropped, recreated, cache cleared, metadata synchronized, previous migrations deleted, migration generated, and migrations applied successfully."
exit 0
