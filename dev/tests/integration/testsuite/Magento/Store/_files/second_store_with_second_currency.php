<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

// phpcs:ignore Magento2.Security.IncludeFile
require_once __DIR__ . '/second_store.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$store = $objectManager->create(\Magento\Store\Model\Store::class);
if ($storeId = $store->load('fixture_second_store', 'code')->getId()) {
    /** @var \Magento\Config\Model\ResourceModel\Config $configResource */
    $configResource = $objectManager->get(\Magento\Config\Model\ResourceModel\Config::class);
    $configResource->saveConfig(
        \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_DEFAULT,
        'EUR',
        \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
        $storeId
    );
    $configResource->saveConfig(
        \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_ALLOW,
        'EUR',
        \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
        $storeId
    );
    /**
     * Configuration cache clean is required to reload currency setting
     */
    /** @var Magento\Config\App\Config\Type\System $config */
    $config = $objectManager->get(\Magento\Config\App\Config\Type\System::class);
    $config->clean();
}


/** @var \Magento\Directory\Model\ResourceModel\Currency $rate */
$rate = $objectManager->create(\Magento\Directory\Model\ResourceModel\Currency::class);
$rate->saveRates(
    [
        'USD' => ['EUR' => 2],
        'EUR' => ['USD' => 0.5]
    ]
);
