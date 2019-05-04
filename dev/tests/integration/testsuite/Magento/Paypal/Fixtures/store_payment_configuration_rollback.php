<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Api\StoreRepositoryInterface;
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

/** @var StoreRepositoryInterface $storeRepository */
$storeRepository = $objectManager->get(StoreRepositoryInterface::class);
$store = $storeRepository->get('test');
$deleteConfigData($configWriter, $configData, ScopeInterface::SCOPE_STORES, (int)$store->getId());
require __DIR__ . '/../../Store/_files/store_rollback.php';
