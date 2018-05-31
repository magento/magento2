<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use Magento\TestFramework\Db\Sequence;
use Magento\TestFramework\Helper\Bootstrap;

$websiteCodes = ['eu_website', 'us_website', 'global_website'];

$objectManager = Bootstrap::getObjectManager();
/** @var ManagerInterface $eventManager */
$eventManager = $objectManager->create(ManagerInterface::class);
/** @var Sequence $sequence */
$sequence = $objectManager->create(Sequence::class);
/** @var StoreInterface $store */
$store = $objectManager->create(Store::class);
$store->load('default');
$rootCategoryId = $store->getRootCategoryId();

foreach ($websiteCodes as $key => $websiteCode) {
    /** @var Website $website */
    $website = $objectManager->create(Website::class);
    $website->setData([
        'code' => $websiteCode,
        'name' => 'Test Website ' . $websiteCode,
        'is_default' => '0',
    ]);
    $website->save();

    $store = $objectManager->create(Store::class);
    $store->setCode(
        'store_for_' . $websiteCode
    )->setWebsiteId(
        $website->getId()
    )->setName(
        'store_for_' . $websiteCode
    )->setSortOrder(
        10 + $key
    )->setIsActive(
        1
    );

    /** @var GroupInterface $group */
    $group = $objectManager->create(GroupInterface::class);
    $group->setName('store_view_' . $websiteCode);
    $group->setCode('store_view_' . $websiteCode);
    $group->setWebsiteId($website->getId());
    $group->setDefaultStoreId($store->getId());
    $group->setRootCategoryId($rootCategoryId);
    $group->save();

    $website->setDefaultGroupId($group->getId());
    $website->save();
    $store->setGroupId($group->getId());
    $store->save();
    $eventManager->dispatch('store_add', ['store' => $store]);
}

/**
 * \Magento\TestFramework\Application by default generates 10 Sequence tables of each kind
 * with indexes form 0 to 9, but last created Store id is greater than 9.
 */
$sequence->generateSequences($store->getId());
