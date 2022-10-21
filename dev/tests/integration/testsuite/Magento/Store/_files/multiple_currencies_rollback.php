<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Config\App\Config\Type\System as Config;
use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Directory\Model\Currency;
use Magento\Directory\Model\ResourceModel\Currency as CurrencyResource;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Store $store */
$store = $objectManager->create(Store::class);
$storeId = $store->load('default', 'code')->getId();

if ($storeId) {
    /** @var ConfigResource $configResource */
    $configResource = $objectManager->get(ConfigResource::class);

    $configResource->deleteConfig(
        Currency::XML_PATH_CURRENCY_ALLOW,
        ScopeInterface::SCOPE_STORES,
        $storeId
    );

    /** @var Config $config */
    $config = $objectManager->get(Config::class);
    $config->clean();

    $reflectionClass = new \ReflectionClass(CurrencyResource::class);
    $staticProperty = $reflectionClass->getProperty('_rateCache');
    $staticProperty->setAccessible(true);
    $staticProperty->setValue(null);
}
