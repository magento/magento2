<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Store\Api\StoreRepositoryInterface;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var StoreRepositoryInterface $storeRepository */
$storeRepository = $objectManager->create(StoreRepositoryInterface::class);

$configValues = [
    [
        'scope' => 'stores',
        'store_code' => 'default',
        'path' => 'carriers/flatrate/price',
        'value' => 5
    ],
    [
        'scope' => 'stores',
        'store_code' => 'fixture_second_store',
        'path' => 'carriers/flatrate/price',
        'value' => 10
    ],
];

foreach ($configValues as $configValue) {
    /** @var \Magento\Framework\App\Config\Value $value */
    $value = $objectManager->create(\Magento\Framework\App\Config\Value::class);
    $value->setScope($configValue['scope']);
    $value->setScopeId($storeRepository->get($configValue['store_code'])->getId());
    $value->setPath($configValue['path']);
    $value->setValue($configValue['value']);
    $value->save();
}

/** @var \Magento\Framework\App\Config\ReinitableConfigInterface $reinitableConfig */
$reinitableConfig = $objectManager->get(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
$reinitableConfig->reinit();
