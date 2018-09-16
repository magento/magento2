<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Store\Model\Website;
use Magento\Store\Model\Store;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndex;
use Magento\Framework\App\Config\ReinitableConfigInterface;

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

//Deleting second website's store.
$store = $objectManager->create(Store::class);
if ($store->load('fixture_second_store', 'code')->getId()) {
    $store->delete();
}

//Deleting the second website.

$configResource = $objectManager->get(\Magento\Config\Model\ResourceModel\Config::class);
//Restoring allowed countries.
$configResource->deleteConfig(
    'general/country/allow',
    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
    1
);
/** @var Website $website */
$website = $objectManager->create(Website::class);
$website->load('test');
if ($website->getId()) {
    $configResource->deleteConfig(
        'general/country/allow',
        \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
        $website->getId()
    );
    $website->delete();
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

/* Refresh stores memory cache */
/** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
$storeManager->reinitStores();
/* Refresh CatalogSearch index */
/** @var \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry */
$indexerRegistry = $objectManager->create(\Magento\Framework\Indexer\IndexerRegistry::class);
$indexerRegistry->get(FulltextIndex::INDEXER_ID)->reindexAll();
//Clear config cache.
$objectManager->get(ReinitableConfigInterface::class)->reinit();
