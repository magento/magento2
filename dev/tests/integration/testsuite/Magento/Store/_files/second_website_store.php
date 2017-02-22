<?php
/**
 * Second website and store fixture
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $website \Magento\Store\Model\Website */
$website = $objectManager->create(\Magento\Store\Model\Website::class);

if (!$website->load('second_website', 'code')->getId()) {
    $website->setData(['code' => 'second_website', 'name' => 'Second Website', 'is_default' => '0']);
    $website->save();
}

$websiteId = $website->getId();

$group = $objectManager->create(\Magento\Store\Model\Group::class);

if (!$group->load('Second Group', 'name')->getId()) {
    $group->setData(['website_id' => $websiteId, 'name' => 'Second Group', 'root_category_id' => '2']);
    $group->save();
}

$groupId = $group->getId();

$store = $objectManager->create(\Magento\Store\Model\Store::class);
$storeId = $store->load('fixture_second_store', 'code')->getId();

if (!$storeId) {
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

    $eventManager = $objectManager->create(\Magento\Framework\Event\ManagerInterface::class);
    $eventName = 'store_add';
    $eventManager->dispatch($eventName, ['store' => $store]);

    /* Refresh stores memory cache */
    $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->reinitStores();
}
