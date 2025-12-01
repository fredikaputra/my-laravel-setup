#!/bin/bash

sed -i 's/php artisan serve/php artisan schedule:work/g' composer.json
sed -i 's/\\"npm run dev\\" --names=server,queue,logs,vite/--names=schedule,queue,logs/g' composer.json
sed -i '/npm/d' composer.json
npx prettier --write composer.json

composer require laravel/fortify
composer require laravel/telescope --dev
php artisan telescope:install
php artisan install:api --passport
php artisan fortify:install
php artisan sail:publish
php artisan migrate:sequence

cp my-laravel-setup/.env.example .env.example
cp my-laravel-setup/ArchTest.php tests/Unit/ArchTest.php
cp my-laravel-setup/compose.yaml compose.yaml
cp my-laravel-setup/devcontainer.json .devcontainer
cp my-laravel-setup/phpunit.xml phpunit.xml
cp my-laravel-setup/bootstrap/* bootstrap
cp my-laravel-setup/migrations/* database/migrations
cp my-laravel-setup/Providers/* app/Providers
cp my-laravel-setup/routes/* routes
cp my-laravel-setup/workflows/* .github/workflows
mv docker/8.4/* .devcontainer

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

git add .
