<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Config\Model\ResourceModel\Config;
use Magento\Directory\Model\Currency as ModelCurrency;
use Magento\Directory\Model\ResourceModel\Currency as ResourceCurrency;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();
$store = $objectManager->create(Store::class);
$storeId = $store->load('fixture_second_store', 'code')->getId();

if ($storeId) {
    $configResource = $objectManager->get(Config::class);
    $configResource->deleteConfig(
        ModelCurrency::XML_PATH_CURRENCY_DEFAULT,
        ScopeInterface::SCOPE_STORES,
        $storeId
    );
    $configResource->deleteConfig(
        ModelCurrency::XML_PATH_CURRENCY_ALLOW,
        ScopeInterface::SCOPE_STORES,
        $storeId
    );
}

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/second_store_rollback.php');
$reflectionClass = new \ReflectionClass(ResourceCurrency::class);
$staticProperty = $reflectionClass->getProperty('_rateCache');
$staticProperty->setAccessible(true);
$staticProperty->setValue(null, null);
