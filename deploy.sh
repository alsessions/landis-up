#!/bin/bash
# Exit on errors, unset variables, and failed pipelines.
set -euo pipefail

# --- Configuration ---
# The name of the Git branch you want to deploy.
BRANCH="main"

echo "--- Starting Git & Craft CMS deployment ---"

# Pull the latest code from the configured 'origin' remote on the specified branch.
echo "Pulling latest code from Git branch '$BRANCH'..."
git pull --ff-only origin "$BRANCH"

# Install or update Composer dependencies.
# The "--no-dev" flag ensures only production dependencies are installed.
echo "Installing Composer dependencies..."
php8.3 "$(command -v composer)" install --no-interaction --no-progress --no-dev

# Move media before project config removes the source volume.
echo "Applying pre-project-config content migrations..."
php8.3 craft migrate/up --track=content --interactive=0

# Apply pending Craft and plugin database migrations, and project config changes.
echo "Applying database migrations and project config..."
php8.3 craft up --interactive=0

# Clear Craft's caches to ensure all changes take effect immediately.
echo "Clearing Craft caches..."
php8.3 craft clear-caches/all

echo "--- Deployment finished successfully ---"
