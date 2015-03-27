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
    $usage = 'Usage: php -f pub/cron.php -- [--group=<groupId>]' . PHP_EOL;
    $longOpts = [
        'help',
        'group::',
        'standaloneProcessStarted::'
    ];
    $opt = getopt('', $longOpts);
    if (isset($opt['help'])) {
        echo $usage;
        exit(0);
    }
}
if (empty($opt['group'])) {
    $opt['group'] = 'default';
}
// It is tracked for internal communication between processes, no user input is needed for this
if (empty($opt['standaloneProcessStarted'])) {
    $opt['standaloneProcessStarted'] = '0';
}

try {
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
