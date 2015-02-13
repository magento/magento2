<?php
/**
 * Scheduled jobs entry point
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\Bootstrap;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;

require dirname(__DIR__) . '/app/bootstrap.php';
$params = $_SERVER;
$params[StoreManager::PARAM_RUN_CODE] = 'admin';
$params[Store::CUSTOM_ENTRY_POINT_PARAM] = true;
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
/** @var \Magento\Framework\App\Cron $app */
$app = $bootstrap->createApplication('Magento\Framework\App\Cron', ['parameters' => ['group::']]);
$bootstrap->run($app);
