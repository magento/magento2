<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;

/** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
$storeManager = Bootstrap::getObjectManager()->get(\Magento\Store\Model\StoreManagerInterface::class);

/** @var \Magento\Store\Model\Store $store */
$store = Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
$storeCode = 'fixturestore';

if (!$store->load($storeCode)->getId()) {
    $store->setCode($storeCode)
        ->setWebsiteId($storeManager->getWebsite()->getId())
        ->setGroupId($storeManager->getWebsite()->getDefaultGroupId())
        ->setName('Fixture Store')
        ->setSortOrder(10)
        ->setIsActive(1);
    $store->save();
}

//if test using this fixture relies on full text functionality it is required to explicitly perform re-indexation
