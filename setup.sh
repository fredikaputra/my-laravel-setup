#!/bin/bash

sed -i 's/#93c5fd,#c4b5fd,#fb7185,#fdba74/#fdba74,#c4b5fd,#fb7185/g' composer.json
sed -i 's/php artisan serve/php artisan schedule:work/g' composer.json
sed -i 's/\\"npm run dev\\" --names=server,queue,logs,vite/--names=schedule,queue,logs/g' composer.json
sed -i '/boost/d' composer.json
sed -i '/npm/d' composer.json
npx prettier --write composer.json

composer require fredikaputra/activity-logger \
                fredikaputra/socialite-boilerplate \
                fredikaputra/async-logger \
                wildside/userstamps \
                laravel/socialite \
                laravel/fortify \
                laravel/nightwatch \
                dedoc/scramble
composer require laravel/telescope --dev
composer update
php artisan telescope:install
php artisan install:api --passport
php artisan fortify:install
php artisan sail:publish

read -p 'Enter to continue...'
git add .

MIG_DIR="database/migrations"
PREFIX="0001_01_01_"

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

composer lint

read -p 'Enter to continue...'
git add .

cp -r my-laravel-setup/src/* .
mv docker/8.5/* .devcontainer

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
        my-laravel-setup

read -p 'Enter to setup Laravel Boost...'
git add .

echo 'AGENTS.md' >> .gitignore
echo 'boost.json' >> .gitignore
echo 'opencode.json' >> .gitignore

composer require laravel/boost --dev
php artisan boost:install