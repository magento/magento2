<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager           = Bootstrap::getObjectManager();
$shippingConfig          = $objectManager->get(Magento\Shipping\Model\Config::class);
$rollbackConfigKey       = 'test/carriers/disabled_shipment_methods';
$configData              = [];
$disabledShipmentMethods = [];

// Get all active Shipping Methods
foreach ($shippingConfig->getAllCarriers() as $carrierCode => $carrierModel) {
    if (!$carrierModel->isActive()) {
        continue;
    }

    $carrierConfigKey              = sprintf('carriers/%s/active', $carrierCode);
    $configData[$carrierConfigKey] = 0;
    $disabledShipmentMethods[]     = $carrierCode;
}

// Remember all manually disabled Shipping Methods for rollback
$configData[$rollbackConfigKey] = implode(',', $disabledShipmentMethods);

/** @var Config $defConfig */
$defConfig = $objectManager->create(Config::class);
$defConfig->setScope(ScopeConfigInterface::SCOPE_TYPE_DEFAULT);

foreach ($configData as $key => $value) {
    $defConfig->setDataByPath($key, $value);
    $defConfig->save();
}
