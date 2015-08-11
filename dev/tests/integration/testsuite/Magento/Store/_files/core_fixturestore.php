<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;

$store = Bootstrap::getObjectManager()->create('Magento\Store\Model\Store');
$storeCode = 'fixturestore';
if (!$store->load($storeCode)->getId()) {
    $websiteId = Bootstrap::getObjectManager()->get('Magento\Store\Model\StoreManagerInterface')
        ->getWebsite()
        ->getId();

    $groupId = Bootstrap::getObjectManager()->get('Magento\Store\Model\StoreManagerInterface')
        ->getWebsite()
        ->getDefaultGroupId();

    $store->setCode($storeCode)
        ->setWebsiteId($websiteId)
        ->setGroupId($groupId)
        ->setName('Fixture Store')
        ->setSortOrder(10)
        ->setIsActive(1);
    $store->save();

    /* Refresh stores memory cache */
    Bootstrap::getObjectManager()->get('Magento\Store\Model\StoreManagerInterface')->reinitStores();
}
