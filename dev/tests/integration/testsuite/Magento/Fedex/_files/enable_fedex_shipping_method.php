<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Config\ScopeConfigInterface;

$objectManager = Bootstrap::getObjectManager();
/** @var Writer $configWriter */
$configWriter = $objectManager->get(WriterInterface::class);

/** @var $mutableScopeConfig */
$mutableScopeConfig = $objectManager->get(
    \Magento\Framework\App\Config\MutableScopeConfigInterface::class
);

/**
 * Retrieve data from TESTS_GLOBAL_CONFIG_FILE
 */
$fedexAccount = $mutableScopeConfig->getValue('carriers/fedex/account', 'store');
$fedexMeterNumber = $mutableScopeConfig->getValue('carriers/fedex/meter_number', 'store');
$fedexKey = $mutableScopeConfig->getValue('carriers/fedex/key', 'store');
$fedexPassword = $mutableScopeConfig->getValue('carriers/fedex/password', 'store');
$fedexEndpointUrl = $mutableScopeConfig->getValue('carriers/fedex/production_webservices_url', 'store');

$configWriter->save('carriers/fedex/active', 1);
$configWriter->save('carriers/fedex/account', $fedexAccount);
$configWriter->save('carriers/fedex/meter_number', $fedexMeterNumber);
$configWriter->save('carriers/fedex/key', $fedexKey);
$configWriter->save('carriers/fedex/password', $fedexPassword);
$configWriter->save('carriers/fedex/production_webservices_url', $fedexEndpointUrl);

$scopeConfig = $objectManager->get(ScopeConfigInterface::class);
$scopeConfig->clean();
