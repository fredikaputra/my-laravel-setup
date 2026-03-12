#!/bin/bash

cat << 'EOF' >> ~/.bash_aliases
alias composer-latest='composer require $(composer show -s --format=json | jq -r ".requires | keys[]" | grep -v "^php$")'
alias composer-latest-dev='composer require --dev $(composer show -s --format=json | jq -r ".devRequires | keys[]")'
EOF

source ~/.bash_aliases

clear

CURRENT_FOLDER=$(basename "$PWD")

mv .git ..

cd ..

rm -rf "$CURRENT_FOLDER"

git clone https://github.com/nunomaduro/laravel-starter-kit-inertia-react "$CURRENT_FOLDER"

rm -rf "$CURRENT_FOLDER/.git"

mv .git "$CURRENT_FOLDER"

cd "$CURRENT_FOLDER"

read -p 'Enter...'
git add .

composer-latest
composer-latest-dev
bun update --latest
echo '{}' > .oxlintrc.json

read -p 'Enter...'
git add .

echo 'Try run composer lint and test'

cp .env.example .env

APP_NAME_VALUE=$(grep '^APP_NAME=' .env | cut -d '=' -f2- | tr -d '"' | tr -d "'")

sed -i "s|\${APP_NAME}|$APP_NAME_VALUE|g" .env
sed -i "s|^APP_URL=.*|APP_URL=https://9002-$WEB_HOST|" .env
sed -i "s|^ASSET_URL=.*|ASSET_URL=https://9002-$WEB_HOST|" .env
sed -i "s|^HMR_HOST=.*|HMR_HOST=5173-$WEB_HOST|" .env

> public/hot

php artisan key:generate

read -p 'Enter...'
git add .