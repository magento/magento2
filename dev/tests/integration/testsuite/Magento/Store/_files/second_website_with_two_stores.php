<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$website = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Website::class);
/** @var $website \Magento\Store\Model\Website */
if (!$website->load('test', 'code')->getId()) {
    $website->setData(['code' => 'test', 'name' => 'Test Website', 'default_group_id' => '1', 'is_default' => '0']);
    $website->save();
}
$websiteId = $website->getId();
$store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
if (!$store->load('fixture_second_store', 'code')->getId()) {
    $groupId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        \Magento\Store\Model\StoreManagerInterface::class
    )->getWebsite()->getDefaultGroupId();
    $store->setCode(
        'fixture_second_store'
    )->setWebsiteId(
        $websiteId
    )->setGroupId(
        $groupId
    )->setName(
        'Fixture Second Store'
    )->setSortOrder(
        10
    )->setIsActive(
        1
    );
    $store->save();
}

$store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Store\Model\Store::class);
if (!$store->load('fixture_third_store', 'code')->getId()) {
    $groupId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
        \Magento\Store\Model\StoreManagerInterface::class
    )->getWebsite()->getDefaultGroupId();
    $store->setCode(
        'fixture_third_store'
    )->setWebsiteId(
        $websiteId
    )->setGroupId(
        $groupId
    )->setName(
        'Fixture Third Store'
    )->setSortOrder(
        11
    )->setIsActive(
        1
    );
    $store->save();
}
/* Refresh stores memory cache */
\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Store\Model\StoreManagerInterface::class
)->reinitStores();

/* Refresh CatalogSearch index */
/** @var \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry */
$indexerRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Framework\Indexer\IndexerRegistry::class);
$indexerRegistry->get(\Magento\CatalogSearch\Model\Indexer\Fulltext::INDEXER_ID)->reindexAll();
