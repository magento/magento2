<?php
/**
 * Create fixture store
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Store');
if (!$store->load('fixture_second_store', 'code')->getId()) {
    $websiteId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        'Magento\Store\Model\StoreManagerInterface'
    )->getWebsite()->getId();
    $groupId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        'Magento\Store\Model\StoreManagerInterface'
    )->getWebsite()->getDefaultGroupId();
    $store->setCode(
        'fixture_second_store'
    )->setWebsiteId(
        $websiteId
    )->setGroupId(
        $groupId
    )->setName(
        'Fixture Store'
    )->setSortOrder(
        10
    )->setIsActive(
        1
    );
    $store->save();
}

/* Refresh stores memory cache */
\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    'Magento\Store\Model\StoreManagerInterface'
)->reinitStores();
