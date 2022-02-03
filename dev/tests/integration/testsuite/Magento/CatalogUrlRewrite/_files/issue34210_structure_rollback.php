<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\StoreInterfaceFactory;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\Data\WebsiteInterfaceFactory;
use Magento\Store\Model\ResourceModel\Store as StoreResource;
use Magento\Store\Model\ResourceModel\Website as WebsiteResource;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var WebsiteResource $websiteResource */
$websiteResource = $objectManager->get(WebsiteResource::class);
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
/** @var StoreResource $storeResource */
$storeResource = $objectManager->get(StoreResource::class);
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var WebsiteInterface $website */
$website = $objectManager->get(WebsiteInterfaceFactory::class)->create();
$websiteResource->load($website, 'che', 'code');
if ($website->getId()) {
    $websiteResource->delete($website);
}

$website = $objectManager->get(WebsiteInterfaceFactory::class)->create();
$websiteResource->load($website, 'exp', 'code');
if ($website->getId()) {
    $websiteResource->delete($website);
}
/** @var StoreInterface $store */
$store = $objectManager->get(StoreInterfaceFactory::class)->create();

foreach (['de_ch', 'en_ch', 'en_ch', 'es_ch', 'fr_ch', 'zh_ch',
             'de_ex', 'en_ex', 'es_ex', 'fr_ex', 'zh_ex'] as $storeCode) {
    $storeResource->load($store, $storeCode, 'code');
    if ($store->getId()) {
        $storeResource->delete($store);
    }
}

/** @var IndexerRegistry $indexerRegistry */
$storeManager->reinitStores();
$indexerRegistry = $objectManager->get(IndexerRegistry::class);
$indexerRegistry->get(Fulltext::INDEXER_ID)->reindexAll();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
