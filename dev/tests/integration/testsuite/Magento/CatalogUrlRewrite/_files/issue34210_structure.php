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
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\StoreInterfaceFactory;
use \Magento\Store\Api\Data\WebsiteInterface;
use \Magento\Store\Api\Data\WebsiteInterfaceFactory;
use Magento\Store\Model\ResourceModel\Group as GroupResource;
use Magento\Store\Model\ResourceModel\Store as StoreResource;
use Magento\Store\Model\ResourceModel\Website as WebsiteResource;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
/** @var WebsiteResource $websiteResource */
$websiteResource = $objectManager->get(WebsiteResource::class);
/** @var StoreResource $storeResource */
$storeResource = $objectManager->get(StoreResource::class);
/** @var GroupResource $groupResource */
$groupResource = $objectManager->get(GroupResource::class);
/** @var DefaultCategory $defaultCategory */
$defaultCategory = $objectManager->get(DefaultCategory::class);
/** @var WebsiteInterface $website */


$website = $objectManager->get(WebsiteInterfaceFactory::class)->create();
$website->setCode('che')->setName('Test Website');
$websiteResource->save($website);
/** @var GroupInterface $storeGroup */
$storeGroup = $objectManager->get(GroupInterfaceFactory::class)->create();
$storeGroup->setCode('che_group')
    ->setRootCategoryId($defaultCategory->getId())
    ->setName('second store group')
    ->setWebsite($website);
$groupResource->save($storeGroup);



$websiteExp = $objectManager->get(WebsiteInterfaceFactory::class)->create();
$websiteExp->setCode('exp')->setName('Test Website');
$websiteResource->save($websiteExp);
/** @var GroupInterface $storeGroup */
$storeGroupExp = $objectManager->get(GroupInterfaceFactory::class)->create();
$storeGroupExp->setCode('exp_group')
    ->setRootCategoryId($defaultCategory->getId())
    ->setName('second store group')
    ->setWebsite($website);
$groupResource->save($storeGroupExp);



/* Refresh stores memory cache */
$storeManager->reinitStores();

foreach (['de_ch', 'en_ch', 'es_ch', 'fr_ch', 'zh_ch'] as $storeCode) {
    /** @var StoreInterface $store */
    $store = $objectManager->get(StoreInterfaceFactory::class)->create();
    $store->setCode($storeCode)
        ->setWebsiteId($website->getId())
        ->setGroupId($storeGroup->getId())
        ->setName('Store Code' . $storeCode)
        ->setIsActive(1);
    $storeResource->save($store);
}


foreach (['de_ex', 'en_ex', 'es_ex', 'fr_ex', 'zh_ex'] as $storeCode) {
    /** @var StoreInterface $store */
    $store = $objectManager->get(StoreInterfaceFactory::class)->create();
    $store->setCode($storeCode)
        ->setWebsiteId($websiteExp->getId())
        ->setGroupId($storeGroupExp->getId())
        ->setName('Store Code' . $storeCode)
        ->setIsActive(1);
    $storeResource->save($store);
}

/* Refresh CatalogSearch index */
/** @var IndexerRegistry $indexerRegistry */
$storeManager->reinitStores();
$indexerRegistry = $objectManager->get(IndexerRegistry::class);
$indexerRegistry->get(Fulltext::INDEXER_ID)->reindexAll();
