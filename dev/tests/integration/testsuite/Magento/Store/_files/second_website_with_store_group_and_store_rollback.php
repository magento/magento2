<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\StoreInterfaceFactory;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\Data\WebsiteInterfaceFactory;
use Magento\Store\Model\ResourceModel\Store as StoreResource;
use Magento\Store\Model\ResourceModel\Website as WebsiteResource;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var WebsiteResource $websiteResource */
$websiteResource = $objectManager->get(WebsiteResource::class);
/** @var StoreResource $storeResource */
$storeResource = $objectManager->get(StoreResource::class);
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var WebsiteInterface $website */
$website = $objectManager->get(WebsiteInterfaceFactory::class)->create();
$websiteResource->load($website, 'test', 'code');
if ($website->getId()) {
    $websiteResource->delete($website);
}
/** @var StoreInterface $store */
$store = $objectManager->get(StoreInterfaceFactory::class)->create();
$storeResource->load($store, 'fixture_second_store', 'code');
if ($store->getId()) {
    $storeResource->delete($store);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
