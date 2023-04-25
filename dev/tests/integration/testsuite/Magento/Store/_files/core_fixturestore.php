<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Store\Model\ResourceModel\Store as StoreResource;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
/** @var StoreFactory $storeFactory */
$storeFactory = $objectManager->get(StoreFactory::class);
/** @var StoreResource $storeResource */
$storeResource = $objectManager->get(StoreResource::class);
$storeCode = 'fixturestore';

$store = $storeFactory->create();
$store->setCode($storeCode)
    ->setWebsiteId($storeManager->getWebsite()->getId())
    ->setGroupId($storeManager->getWebsite()->getDefaultGroupId())
    ->setName('Fixture Store')
    ->setSortOrder(10)
    ->setIsActive(1);
$storeResource->save($store);

$storeManager->reinitStores();
//if test using this fixture relies on full text functionality it is required to explicitly perform re-indexation
