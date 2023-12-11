<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Store\Model\Website;
use Magento\Store\Model\Store;
use Magento\Store\Model\Group;

$objectManager = Bootstrap::getObjectManager();
//Creating second website with a store.
/** @var $website \Magento\Store\Model\Website */
$website = $objectManager->create(Website::class);
$website->load('second', 'code');

if (!$website->getId()) {
    $website->setData([
        'code' => 'second',
        'name' => 'Second Test Website',
        'is_default' => '0',
    ]);
    $website->save();
}

/**
 * @var Group $storeGroup
 */
$storeGroup = $objectManager->create(Group::class);
$storeGroup->setCode('second_store')
    ->setName('Second store group')
    ->setWebsite($website);
$storeGroup->save();

$website->setDefaultGroupId($storeGroup->getId());
$website->save();

$websiteId = $website->getId();
$store = $objectManager->create(Store::class);
$store->load('second_store_view', 'code');

if (!$store->getId()) {
    $groupId = $website->getDefaultGroupId();
    $store->setData([
        'code' => 'second_store_view',
        'website_id' => $websiteId,
        'group_id' => $groupId,
        'name' => 'Second Store View',
        'sort_order' => 10,
        'is_active' => 1,
    ]);
    $store->save();
}

//Creating third website with a store and a storeview
/** @var $website2 \Magento\Store\Model\Website */
$website2 = $objectManager->create(Website::class);
$website2->load('third', 'code');

if (!$website2->getId()) {
    $website2->setData([
        'code' => 'third',
        'name' => 'Third test Website',
        'is_default' => '0',
    ]);
    $website2->save();
}

/**
 * @var Group $storeGroup2
 */
$storeGroup2 = $objectManager->create(Group::class);
$storeGroup2->setCode('third_store')
    ->setName('Third store group')
    ->setWebsite($website2);
$storeGroup2->save($storeGroup2);

$website2->setDefaultGroupId($storeGroup2->getId());
$website2->save($website2);

$websiteId2 = $website2->getId();
$store2 = $objectManager->create(Store::class);
$store2->load('third_store_view', 'code');

if (!$store2->getId()) {
    $groupId = $website2->getDefaultGroupId();
    $store2->setData([
        'code' => 'third_store_view',
        'website_id' => $websiteId2,
        'group_id' => $groupId,
        'name' => 'Third Store view',
        'sort_order' => 10,
        'is_active' => 1,
    ]);
    $store2->save();
}
