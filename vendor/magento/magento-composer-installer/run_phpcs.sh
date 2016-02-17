#!/usr/bin/env sh
#needs to do 
#cp /vendor/firegento/phpcs /vendor/firegento/FireGento
#before
./vendor/bin/phpcs --report-full=report.full --standard=./vendor/firegento/FireGento -p  ./src
