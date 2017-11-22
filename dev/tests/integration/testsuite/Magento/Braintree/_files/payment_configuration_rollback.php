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

$objectManager = Bootstrap::getObjectManager();

$deleteConfigData = function (WriterInterface $writer, $scope, $scopeId) {
    $configData = [
        'payment/braintree/merchant_id',
        'payment/braintree/public_key',
        'payment/braintree/private_key',
    ];
    foreach ($configData as $path) {
        $writer->delete($path, $scope, $scopeId);
    }
};

/** @var WriterInterface $configWriter */
$configWriter = $objectManager->get(WriterInterface::class);
$deleteConfigData($configWriter, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null);

/** @var StoreRepositoryInterface $storeRepository */
$storeRepository = $objectManager->get(StoreRepositoryInterface::class);
$store = $storeRepository->get('test');
$deleteConfigData($configWriter, ScopeInterface::SCOPE_STORES, $store->getId());

/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$website = $websiteRepository->get('test');
$deleteConfigData($configWriter, ScopeInterface::SCOPE_WEBSITES, $website->getId());

require __DIR__ . '/../../Store/_files/second_website_with_two_stores_rollback.php';
require __DIR__ . '/../../Store/_files/store_rollback.php';
