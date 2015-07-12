<?php
/**
 * Scheduled jobs entry point
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;

require dirname(__DIR__) . '/app/bootstrap.php';

if ($_GET){
    $opt = $_GET;
} else {
    echo "You cannot run this from the command line." . PHP_EOL .
        "Run \"php bin/magento cron:run\" instead." . PHP_EOL;
    exit(1);
}

try {
    if (empty($opt['group'])) {
        $opt['group'] = 'default';
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
