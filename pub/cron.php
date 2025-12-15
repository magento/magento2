<?php
/**
 * Scheduled jobs entry point
 *
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

// Security check: Block HTTP access for security and to prevent errors
// This must be checked before any bootstrapping to prevent errors
if (isset($_SERVER['REQUEST_METHOD'])) {
    // This is a web request - block it completely
    if (!headers_sent()) {
        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: text/plain');
    }
    echo "Forbidden: This script is not intended for web access.\n";
    echo "Use command line: php bin/magento cron:run\n";
    exit(1);
}

use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;

require dirname(__DIR__) . '/app/bootstrap.php';

if (php_sapi_name() === 'cli') {
    echo "Please use the recommended command instead:" . PHP_EOL .
        "php bin/magento cron:run" . PHP_EOL;
    exit(1);
} else {
    $opt = $_GET;
}

try {
    foreach ($opt as $key => $value) {
        $opt[$key] = escapeshellarg($value);
    }
    $opt['standaloneProcessStarted'] = '0';
    $params = $_SERVER;
    $params[StoreManager::PARAM_RUN_CODE] = 'admin';
    $params[Store::CUSTOM_ENTRY_POINT_PARAM] = true;
    $bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
    /** @var \Magento\Framework\App\Cron $app */
    $app = $bootstrap->createApplication(\Magento\Framework\App\Cron::class, ['parameters' => $opt]);
    $bootstrap->run($app);
} catch (\Exception $e) {
    echo $e;
    exit(1);
}
