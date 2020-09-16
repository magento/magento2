<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$store = $objectManager->create(\Magento\Store\Model\Store::class);
$storeId = $store->load('fixture_second_store', 'code')->getId();

if ($storeId) {
    $configResource = $objectManager->get(\Magento\Config\Model\ResourceModel\Config::class);
    $configResource->deleteConfig(
        \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_DEFAULT,
        \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
        $storeId
    );
    $configResource->deleteConfig(
        \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_ALLOW,
        \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
        $storeId
    );
}

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/second_store_rollback.php');
