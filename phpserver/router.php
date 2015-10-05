<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * this is a router file for the php Built-in web server
 * https://secure.php.net/manual/en/features.commandline.webserver.php
 *
 * It provides the same "rewrites" as the .htaccess for apache,
 * or the nginx.conf.sample for nginx.
 *
 * example usage: php -S 127.0.0.41:8082 -t ./pub/ ./router.php
 */

/**
 * Set it to true to enable debug mode
 */
define('DEBUG_ROUTER', false);

$debug = function ($val) {

    if (!DEBUG_ROUTER) {
        return;
    }

    if (is_array($val)) {
        $val = json_encode($val);
    }

    echo 'debug: '.$val.PHP_EOL.'<br/>'.PHP_EOL;
};

/**
 * Caution, this is very experimental stuff
 * no guarantee for working result
 * has tons of potential big security holes
 */

if (php_sapi_name() === 'cli-server') {

    $debug($_SERVER["REQUEST_URI"]);
    if (preg_match('/^\/(index|get|static)\.php(\/)?/', $_SERVER["REQUEST_URI"])) {
        return false;    // serve the requested resource as-is.
    }

    $path = pathinfo($_SERVER["SCRIPT_FILENAME"]);
    $url   = pathinfo(substr($_SERVER["REQUEST_URI"], 1));
    $route = parse_url(substr($_SERVER["REQUEST_URI"], 1))["path"];

    $debug($path);
    $debug($route);

    if ($path["basename"] == 'favicon.ico') {
        return false;
    }

    $debug($route);
    $debug(strpos($route, 'errors/default/css/'));

    if (strpos($route, 'pub/errors/default/') === 0) {
        $route = preg_replace('#pub/errors/default/#', 'errors/default/', $route, 1);
    }

    $debug($route);

    if (
        strpos($route, 'media/') === 0 ||
        strpos($route, 'opt/') === 0 ||
        strpos($route, 'static/') === 0 ||
        strpos($route, 'errors/default/css/') === 0 ||
        strpos($route, 'errors/default/images/') === 0
    ) {
        $magentoPackagePubDir = __DIR__."/../pub";

        $file = $magentoPackagePubDir.'/'.$route;
        $debug($file);
        if (file_exists($file)) {
            $debug('file exists');
            return false;
        } else {
            $debug('file does not exist');
            if (strpos($route, 'static/') === 0) {
                $route = preg_replace('#static/#', '', $route, 1);
                $_GET['resource'] = $route;
                include($magentoPackagePubDir.'/static.php');
                exit;
            } elseif (strpos($route, 'media/') === 0) {
                include($magentoPackagePubDir.'/get.php');
                exit;
            }
        }
    }

    header('HTTP/1.0 404 Not Found');
}
