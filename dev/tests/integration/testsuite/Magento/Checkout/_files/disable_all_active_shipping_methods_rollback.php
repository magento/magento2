<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$rollbackConfigKey = 'test/carriers/disabled_shipment_methods';

$configWriter = $objectManager->create(WriterInterface::class);
$rollbackConfigValue = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)
    ->getStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID)
    ->getConfig($rollbackConfigKey);

$disabledShipmentMethods = [];
if (!empty($rollbackConfigValue)) {
    $disabledShipmentMethods = explode(',', $rollbackConfigValue);
}

if (count($disabledShipmentMethods)) {
    foreach ($disabledShipmentMethods as $keyToRemove) {
        $configWriter->delete(sprintf('carriers/%s/active', $keyToRemove));
    }
}
$configWriter->delete($rollbackConfigKey);

$scopeConfig = $objectManager->get(ScopeConfigInterface::class);
$scopeConfig->clean();
