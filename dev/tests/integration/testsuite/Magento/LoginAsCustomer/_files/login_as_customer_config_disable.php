<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Config\Model\Config\Factory as ConfigFactory;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$loginAsCustomerConfigPath = 'login_as_customer/general/enabled';
/** @var ConfigFactory $configFactory */
$configFactory = $objectManager->get(ConfigFactory::class);

$configModel = $configFactory->create();
$configModel->setDataByPath($loginAsCustomerConfigPath, 0);
try {
    $configModel->save();
} catch (Exception $e) {
}
