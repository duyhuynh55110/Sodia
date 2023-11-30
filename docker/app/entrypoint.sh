#!/bin/sh
WORKSPACE="/var/www/app"

chmod -R 777 $WORKSPACE/storage
chmod -R 777 $WORKSPACE/bootstrap

cd $WORKSPACE

# mix assets
npm install
npm run prod

# install package
composer install

# migration
php artisan migrate

# create storage link
php artisan storage:link

# generate app key
php artisan key:generate

# start php-fpm
php-fpm -F