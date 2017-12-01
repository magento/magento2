<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Config\Model\Config\Factory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Factory $configFactory */
$configFactory = $objectManager->create(Factory::class);
/** @var \Magento\Config\Model\Config $config */
$config = $configFactory->create();
$config->setScope('stores');
$config->setStore('default');
$config->setDataByPath('design/header/welcome', null);
$config->save();
