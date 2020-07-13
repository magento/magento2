<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Api\Data\GroupInterfaceFactory;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\StoreInterfaceFactory;
use \Magento\Store\Api\Data\WebsiteInterface;
use \Magento\Store\Api\Data\WebsiteInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);

/** @var WebsiteInterface $website */
$website = $objectManager->create(WebsiteInterfaceFactory::class)->create();
$website->setCode('test')
    ->setName('Test Website')
    ->save();

/** @var GroupInterface $storeGroup */
$storeGroup = $objectManager->create(GroupInterfaceFactory::class)->create();
$storeGroup->setCode('second_group')
    ->setName('second store group')
    ->setWebsite($website);
$storeGroup->save();
/* Refresh stores memory cache */
$storeManager->reinitStores();

/** @var StoreInterface $store */
$store = $objectManager->create(StoreInterfaceFactory::class)->create();
$store->setCode('fixture_second_store')
    ->setWebsiteId($website->getId())
    ->setGroupId($storeGroup->getId())
    ->setName('Fixture Second Store')
    ->setSortOrder(10)
    ->setIsActive(1)
    ->save();
/* Refresh CatalogSearch index */
/** @var IndexerRegistry $indexerRegistry */
$indexerRegistry = $objectManager->create(IndexerRegistry::class);
$indexerRegistry->get(Fulltext::INDEXER_ID)->reindexAll();
