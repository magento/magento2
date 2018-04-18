<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Store\Model\Website;
use Magento\Store\Model\Store;
use Magento\Config\Model\Config\Factory as ConfigFactory;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndex;
use Magento\Framework\App\Config\ReinitableConfigInterface;

//Deleting second website's store.
$store = Bootstrap::getObjectManager()->create(Store::class);
if ($store->load('fixture_second_store', 'code')->getId()) {
    $store->delete();
}

//Deleting the second site.
/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var Website $website */
$website = Bootstrap::getObjectManager()->create(Website::class);
$website->load('test');
if ($website->getId()) {
    $website->delete();
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

//Restoring allowed countries.
/** @var ConfigFactory $configFactory */
$configFactory = Bootstrap::getObjectManager()->get(ConfigFactory::class);
//Allowed countries for default website.
$configData = [
    'section' => 'general',
    'website' => 1,
    'store'   => null,
    'groups'  => [
        'country' => [
            'fields' => [
                'default' => ['inherit' => 1],
                'allow'   => ['inherit' => 1],
            ],
        ],
    ],
];
$configModel = $configFactory->create(['data' => $configData]);
$configModel->save();

/* Refresh stores memory cache */
/** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
$storeManager = Bootstrap::getObjectManager()->get(
    \Magento\Store\Model\StoreManagerInterface::class
);
$storeManager->reinitStores();
/* Refresh CatalogSearch index */
/** @var \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry */
$indexerRegistry = Bootstrap::getObjectManager()
    ->create(\Magento\Framework\Indexer\IndexerRegistry::class);
$indexerRegistry->get(FulltextIndex::INDEXER_ID)->reindexAll();
//Clear config cache.
Bootstrap::getObjectManager()->get(ReinitableConfigInterface::class)->reinit();
