<?php
/**
 * Scheduled jobs entry point
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;

require dirname(__DIR__) . '/app/bootstrap.php';

if (php_sapi_name() === 'cli'){
    echo "You cannot run this from the command line." . PHP_EOL .
        "Run \"php bin/magento cron:run\" instead." . PHP_EOL;
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
    $app = $bootstrap->createApplication('Magento\Framework\App\Cron', ['parameters' => $opt]);
    $bootstrap->run($app);
} catch (\Exception $e) {
    echo $e;
    exit(1);
}
