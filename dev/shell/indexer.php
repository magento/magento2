<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

use Magento\Framework\App\Bootstrap;
use Magento\Store\Model\StoreManager;

require __DIR__ . '/../../app/bootstrap.php';
$params = $_SERVER;
$params[StoreManager::PARAM_RUN_CODE] = 'admin';
$params[StoreManager::PARAM_RUN_TYPE] = 'store';
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $params);
/** @var \Magento\Indexer\App\Shell $application */
$app = $bootstrap->createApplication('Magento\Indexer\App\Shell', ['entryFileName' => basename(__FILE__)]);
$bootstrap->run($app);
