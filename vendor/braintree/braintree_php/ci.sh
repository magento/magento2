#!/bin/bash

curl -sS https://getcomposer.org/installer | php -d suhosin.executor.include.whitelist=phar

php -d suhosin.executor.include.whitelist=phar ./composer.phar install

rake --trace
