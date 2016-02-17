#!/usr/bin/env php
<?php
die('should not get used, only to experiment with stuff');
require_once __DIR__ . '/vendor/autoload.php';

// 2.x release
//require __DIR__ . '/CodeSniffer.conf';
//$GLOBALS['PHP_CODESNIFFER_CONFIG_DATA'] = $phpCodeSnifferConfig;

if( !is_link(__DIR__.'/vendor/firegento/FireGento') ){
    symlink( __DIR__.'/vendor/firegento/phpcs', __DIR__.'/vendor/firegento/FireGento');
}

#$tempSnifferObject = new PHP_CodeSniffer;
#$tempSnifferObject->setTokenListeners( __DIR__.'/vendor/firegento/phpcs');

require_once __DIR__ . '/vendor/bin/phpcs';