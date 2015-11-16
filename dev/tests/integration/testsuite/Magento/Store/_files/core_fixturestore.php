<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;

/** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
$storeManager = Bootstrap::getObjectManager()->get('Magento\Store\Model\StoreManagerInterface');

/** @var \Magento\Store\Model\Store $store */
$store = Bootstrap::getObjectManager()->create('Magento\Store\Model\Store');
$storeCode = 'fixturestore';

if (!$store->load($storeCode)->getId()) {
    $store->setCode($storeCode)
        ->setWebsiteId($storeManager->getWebsite()->getId())
        ->setGroupId($storeManager->getWebsite()->getDefaultGroupId())
        ->setName('Fixture Store')
        ->setSortOrder(10)
        ->setIsActive(1);
    $store->save();

    /* Refresh stores memory cache */
    Bootstrap::getObjectManager()->get('Magento\Store\Model\StoreManagerInterface')->reinitStores();
}
