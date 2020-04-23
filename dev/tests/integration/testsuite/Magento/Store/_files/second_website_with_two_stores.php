<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

$website = Bootstrap::getObjectManager()->create(\Magento\Store\Model\Website::class);

/** @var $website \Magento\Store\Model\Website */
if (!$website->load('test', 'code')->getId()) {
    $website->setData(['code' => 'test', 'name' => 'Test Website', 'is_default' => '0']);
    $website->save();
}
$websiteId = $website->getId();
$store = Bootstrap::getObjectManager()->create(Store::class);
if (!$store->load('fixture_second_store', 'code')->getId()) {
    $store->setCode(
        'fixture_second_store'
    )->setWebsiteId(
        $websiteId
    )->setName(
        'Fixture Second Store'
    )->setSortOrder(
        10
    )->setIsActive(
        1
    );
    $store->save();
}

$storeGroup = Bootstrap::getObjectManager()->create(Group::class);
$storeGroup->setCode('fixture_second_store_group');
$storeGroup->setWebsiteId($websiteId);
$storeGroup->setName('Fixture Second Store Group');
$storeGroup->setDefaultStoreId($store->getId());
$storeGroup->save();

$website->setDefaultGroupId($storeGroup->getId());
$website->save();
$store->setGroupId($storeGroup->getId());
$store->save();

$store = Bootstrap::getObjectManager()->create(Store::class);
if (!$store->load('fixture_third_store', 'code')->getId()) {
    $store->setCode(
        'fixture_third_store'
    )->setWebsiteId(
        $websiteId
    )->setGroupId(
        $storeGroup->getId()
    )->setName(
        'Fixture Third Store'
    )->setSortOrder(
        11
    )->setIsActive(
        1
    );
    $store->save();
}

/* Refresh CatalogSearch index */
$indexerRegistry = Bootstrap::getObjectManager()->create(IndexerRegistry::class);
$indexerRegistry->get(Fulltext::INDEXER_ID)->reindexAll();
