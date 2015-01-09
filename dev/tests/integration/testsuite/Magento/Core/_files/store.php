<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

$store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Store\Model\Store');
$storeCode = 'fixturestore';
if (!$store->load($storeCode)->getId()) {
    $websiteId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        'Magento\Store\Model\StoreManagerInterface'
    )->getWebsite()->getId();
    $groupId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        'Magento\Store\Model\StoreManagerInterface'
    )->getWebsite()->getDefaultGroupId();
    $store->setCode(
        $storeCode
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

    /* Refresh stores memory cache */
    \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        'Magento\Store\Model\StoreManagerInterface'
    )->reinitStores();
}
