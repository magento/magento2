<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventorySalesApi\Test\OriginalSequenceBuilder;
use Magento\SalesSequence\Model\EntityPool;
use Magento\SalesSequence\Model\Config;

$websiteCodes = ['eu_website', 'us_website', 'global_website'];

$objectManager = Bootstrap::getObjectManager();
/**
 * Set original sequence builder to object manager in order to generate sequence table with correct store id.
 */
$sequenceBuilder = $objectManager->get(OriginalSequenceBuilder::class);
$objectManager->addSharedInstance($sequenceBuilder, \Magento\SalesSequence\Model\Builder::class);
/** @var EntityPool $entityPool */
$entityPool = $objectManager->get(EntityPool::class);
/** @var Config $sequenceConfig */
$sequenceConfig = $objectManager->get(Config::class);

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
}

/**
 * Revert set original sequence builder to test sequence builder.
 */
$sequenceBuilder = $objectManager->get(\Magento\TestFramework\Db\Sequence\Builder::class);
$objectManager->addSharedInstance($sequenceBuilder, \Magento\SalesSequence\Model\Builder::class);
