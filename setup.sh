#!/bin/bash

echo '*' > my-laravel-setup/.gitignore

sed -i 's/php artisan serve/php artisan schedule:work/g' composer.json
sed -i 's/\\"npm run dev\\" --names=server,queue,logs,vite/\\"php artisan horizon\\" --names=schedule,queue,logs,horizon/g' composer.json
sed -i '/boost/d' composer.json
sed -i '/npm/d' composer.json
npx prettier --write composer.json

composer require fredikaputra/activity-logger \
                fredikaputra/socialite-boilerplate \
                fredikaputra/async-logger \
                laravel/socialite \
                laravel/fortify \
                laravel/nightwatch \
                laravel/octane \
                laravel/horizon \
                laravel/scout \
                laravel/mcp \
                dedoc/scramble \
                http-interop/http-factory-guzzle \
                meilisearch/meilisearch-php
composer require laravel/telescope laravel/sail laravel/pulse spatie/laravel-web-tinker --dev
composer update
php artisan telescope:install
php artisan install:api --passport
php artisan fortify:install
php artisan sail:publish
php artisan horizon:install
php artisan vendor:publish --tag=ai-routes

git add .

MIG_DIR="database/migrations"
PREFIX="0001_01_01_"

rm database/migrations/0001_01_01_000001_create_cache_table.php
rm database/migrations/0001_01_01_000002_create_jobs_table.php

last_index=$(ls "$MIG_DIR"/"$PREFIX"*.php 2>/dev/null | sed -E "s/.*$PREFIX([0-9]+)_.*/\1/" | sort -rn | head -1)

if [ -z "$last_index" ]; then
    last_index=-1
else
    last_index=$((10#$last_index))
fi

for file in $(ls "$MIG_DIR"/*.php | grep -v "$PREFIX" | sort); do
    last_index=$((last_index + 1))
    
    next_num=$(printf "%06d" "$last_index")
    
    filename=$(basename "$file")
    
    base_name=$(echo "$filename" | sed -E 's/^[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{6}_//')
    
    new_name="${PREFIX}${next_num}_${base_name}"
    
    mv "$file" "$MIG_DIR/$new_name"
done

read -p 'Enter to continue...'
git add .

TARGET_DIR="database/migrations"

if [ ! -d "$TARGET_DIR" ]; then
  echo "Error: Directory $TARGET_DIR does not exist."
  exit 1
fi

find "$TARGET_DIR" -type f -name "*.php" -exec sed -i "s/foreignId('user_id')/foreignUuid('user_id')/g" {} +

read -p 'Enter to continue...'
git add .

rm -r config
cp -r my-laravel-setup/src/. .
cp .env.example .env
mv docker/8.4/* .devcontainer
mv docker/mysql/create-testing-database.sh .devcontainer

read -p 'Enter to continue...'
git add .

composer lint
php artisan migrate:fresh

sed -i '/^\/public\/storage/a\/public\/vendor' .gitignore

node -e "
const fs = require('fs');
const file = 'composer.json';
const json = JSON.parse(fs.readFileSync(file));

json.extra = json.extra || {};
json.extra.laravel = json.extra.laravel || {};
json.extra.laravel['dont-discover'] = ['laravel/telescope'];

fs.writeFileSync(file, JSON.stringify(json, null, 4));
"
echo >> composer.json

rm -rf .cursor \
        .idx \
        .junie \
        art \
        docker \
        public/favicon.ico \
        resources \
        tests/Browser \
        .mcp.json \
        package-lock.json \
        package.json \
        CLAUDE.md \
        README.md \
        vite.config.js \
        app/Actions/.gitkeep \
        app/Providers/HorizonServiceProvider.php \
        app/Providers/TelescopeServiceProvider.php \
        my-laravel-setup

read -p 'Enter to setup Laravel Boost...'
git add .

echo '' >> .gitignore
echo 'AGENTS.md' >> .gitignore
echo 'boost.json' >> .gitignore
echo 'opencode.json' >> .gitignore
echo '.idx' >> .gitignore

composer require laravel/boost --dev
php artisan boost:install
composer test