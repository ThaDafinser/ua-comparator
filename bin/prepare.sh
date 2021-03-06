#!/bin/sh

cd `dirname $0`

echo "clearing results directory ..."
rm -rf ../data/results/

mkdir ../data/results/

echo "clearing browser directory ..."
rm -rf ../data/browser/

mkdir ../data/browser/

echo "clearing cache directory ..."
rm -rf ../data/cache/

mkdir ../data/cache/
mkdir ../data/cache/browscap2/
mkdir ../data/cache/browscap3/
mkdir ../data/cache/browser/
mkdir ../data/cache/crossjoin/
mkdir ../data/cache/piwik/
mkdir ../data/cache/uaparser/
mkdir ../data/cache/uasparser/
mkdir ../data/cache/wurfl/
mkdir ../data/cache/wurfl_old/

echo "Updating ua-parser data..."
php ../vendor/ua-parser/uap-php/bin/uaparser.php ua-parser:update

echo "Creating browscap.ini file..."
php build-browscap.ini.php

echo "Updating php-browscap (2.x) data..."
php update-php-browscap.php

echo "Updating browscap-php (3.x) data..."
php update-browscap-php.php

echo "Updating crossjoin-browscap data..."
php update-crossjoin-browscap.php

echo "Preparing Wurfl data..."
php prepare-wurfl.php

echo "Preparing Wurfl (old) data..."
php prepare-wurfl-old.php
