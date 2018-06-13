<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$deleteConfigData = function (WriterInterface $writer, $scope, $scopeId) {
    $configData = [
        'fraud_protection/signifyd/active',
    ];
    foreach ($configData as $path) {
        $writer->delete($path, $scope, $scopeId);
    }
};

/** @var WriterInterface $configWriter */
$configWriter = $objectManager->get(WriterInterface::class);
$deleteConfigData($configWriter, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null);

/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$website = $websiteRepository->get('test_website');
$deleteConfigData($configWriter, ScopeInterface::SCOPE_WEBSITES, $website->getId());

$website = $objectManager->create(Website::class);
/** @var $website Website */
if ($website->load('test_website', 'code')->getId()) {
    $website->delete();
}
$store = $objectManager->create(Store::class);
if ($store->load('test_second_store', 'code')->getId()) {
    $store->delete();
}
