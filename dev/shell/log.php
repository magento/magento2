<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\Bootstrap;
use Magento\Store\Model\StoreManager;

require __DIR__ . '/../../app/bootstrap.php';
$params = $_SERVER;
$params[StoreManager::PARAM_RUN_CODE] = 'admin';
$params[StoreManager::PARAM_RUN_TYPE] = 'store';
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
/** @var \Magento\Log\App\Shell $app */
$app = $bootstrap->createApplication('Magento\Log\App\Shell', ['entryFileName' => basename(__FILE__)]);
$bootstrap->run($app);
