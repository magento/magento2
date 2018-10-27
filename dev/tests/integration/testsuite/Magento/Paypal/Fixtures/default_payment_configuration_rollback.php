<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/process_config_data.php';

$objectManager = Bootstrap::getObjectManager();

$configData = [
    'payment/payflowpro/partner',
    'payment/payflowpro/vendor',
    'payment/payflowpro/user',
    'payment/payflowpro/pwd',
];
/** @var WriterInterface $configWriter */
$configWriter = $objectManager->get(WriterInterface::class);
$deleteConfigData($configWriter, $configData, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
