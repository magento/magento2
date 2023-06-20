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
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Store\Model\Store;

$objectManager = Bootstrap::getObjectManager();
/** @var Store $store */
$store = $objectManager->create(Store::class);
$storeId = $store->load('default', 'code')->getId();

/** @var ConfigResource $configResource */
$configResource = $objectManager->get(ConfigResource::class);
$configResource->saveConfig(
    Currency::XML_PATH_CURRENCY_ALLOW,
    'USD,EUR',
    ScopeInterface::SCOPE_STORES,
    $storeId
);

/**
 * Configuration cache clean is required to reload currency setting
 */
/** @var Config $config */
$config = $objectManager->get(Config::class);
$config->clean();

/** @var CurrencyResource $currencyResource */
$currencyResource = $objectManager->create(CurrencyResource::class);
$currencyResource->saveRates(
    [
        'USD' => ['EUR' => 2],
        'EUR' => ['USD' => 0.5]
    ]
);
