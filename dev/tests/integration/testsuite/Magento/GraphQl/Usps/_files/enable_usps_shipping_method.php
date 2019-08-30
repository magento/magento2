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
$uspsAccountId = $mutableScopeConfig->getValue('carriers/usps/userid', 'store');
$uspsAccountPassword = $mutableScopeConfig->getValue('carriers/usps/password', 'store');

$configWriter->save('carriers/usps/active', 1);
$configWriter->save('carriers/usps/userid', $uspsAccountId);
$configWriter->save('carriers/usps/password', $uspsAccountPassword);

$scopeConfig = $objectManager->get(ScopeConfigInterface::class);
$scopeConfig->clean();
