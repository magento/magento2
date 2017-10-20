<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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

    error_log('debug: '.$val);
};

/**
 * Caution, this is very experimental stuff
 * no guarantee for working result
 * has tons of potential big security holes
 */

if (php_sapi_name() === 'cli-server') {
    $debug("URI: {$_SERVER["REQUEST_URI"]}");
    if (preg_match('/^\/(index|get|static)\.php(\/)?/', $_SERVER["REQUEST_URI"])) {
        return false;    // serve the requested resource as-is.
    }

    $path = pathinfo($_SERVER["SCRIPT_FILENAME"]);
    $url   = pathinfo(substr($_SERVER["REQUEST_URI"], 1));
    $route = parse_url(substr($_SERVER["REQUEST_URI"], 1))["path"];
    $pathinfo = pathinfo($route);
    $ext = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';

    if ($path["basename"] == 'favicon.ico') {
        return false;
    }

    $debug("route: $route");

    if (strpos($route, 'pub/errors/default/') === 0) {
        $route = preg_replace('#pub/errors/default/#', 'errors/default/', $route, 1);
    }

    $magentoPackagePubDir = __DIR__."/../pub";

    if (strpos($route, 'media/') === 0 ||
        strpos($route, 'opt/') === 0 ||
        strpos($route, 'static/') === 0 ||
        strpos($route, 'errors/default/css/') === 0 ||
        strpos($route, 'errors/default/images/') === 0
    ) {
        $origFile = $magentoPackagePubDir.'/'.$route;

        if (strpos($route, 'static/version') === 0) {
            $route = preg_replace('#static/(version\d+/)?#', 'static/', $route, 1);
        }
        $file = $magentoPackagePubDir.'/'.$route;

        $debug("file: $file");

        if (file_exists($origFile)) {
            $debug('file exists');
            return false;
        } else if (file_exists($file)) {
            $mimeTypes = [
                'css' => 'text/css',
                'js'  => 'application/javascript',
                'jpg' => 'image/jpg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'map' => 'application/json',
                'woff' => 'application/x-woff',
                'woff2' => 'application/font-woff2',
                'html' => 'text/html',
            ];

            $type = isset($mimeTypes[$ext]) && $mimeTypes[$ext];
            if ($type) {
                header("Content-Type: $type");
            }
            readfile($file);
            return;
        } else {
            $debug('file does not exist');
            if (strpos($route, 'static/') === 0) {
                $_GET['resource'] = $route;
                $debug("static: $route");
                include($magentoPackagePubDir.'/static.php');
                exit;
            } elseif (strpos($route, 'media/') === 0) {
                $debug("media: $route");
                include($magentoPackagePubDir.'/get.php');
                exit;
            }
        }
    } else {
        $debug("thunk to index in $route");
        include($magentoPackagePubDir.'/index.php');
    }
}
