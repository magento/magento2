<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Helper\DefaultCategory;
use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Api\Data\GroupInterfaceFactory;
use Magento\Store\Model\ResourceModel\Group as GroupResource;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/second_website_with_two_stores.php');
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var $website \Magento\Store\Model\Website */
$website = $objectManager->create(\Magento\Store\Model\Website::class);
$website->load('test', 'code')->getId();
$websiteId = $website->getId();

/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
/** @var DefaultCategory $defaultCategory */
$defaultCategory = $objectManager->get(DefaultCategory::class);
/** @var GroupInterface $storeGroup */
$storeGroup = $objectManager->get(GroupInterfaceFactory::class)->create();
$storeGroup->setCode('second_group')
    ->setRootCategoryId($defaultCategory->getId())
    ->setName('second store group')
    ->setWebsite($website);
$objectManager->get(GroupResource::class)->save($storeGroup);
/* Refresh stores memory cache */
$storeManager->reinitStores();

$store = $objectManager->create(Store::class);
if (!$store->load('fixture_fourth_store', 'code')->getId()) {
    $store->setCode(
        'fixture_fourth_store'
    )->setWebsiteId(
        $websiteId
    )->setGroupId(
        $storeGroup->getId()
    )->setName(
        'Fixture Fourth Store'
    )->setSortOrder(
        6
    )->setIsActive(
        1
    );
    $store->save();
}

$store = $objectManager->create(Store::class);
if (!$store->load('fixture_fifth_store', 'code')->getId()) {
    $store->setCode(
        'fixture_fifth_store'
    )->setWebsiteId(
        $websiteId
    )->setGroupId(
        $storeGroup->getId()
    )->setName(
        'Fixture Fifth Store'
    )->setSortOrder(
        5
    )->setIsActive(
        1
    );
    $store->save();
}

/* Refresh CatalogSearch index */
/** @var IndexerRegistry $indexerRegistry */
$indexerRegistry = $objectManager->get(IndexerRegistry::class);
$indexerRegistry->get(Fulltext::INDEXER_ID)->reindexAll();
