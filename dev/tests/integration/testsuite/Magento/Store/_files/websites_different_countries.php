<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Config\Model\Config\Factory as ConfigFactory;
use Magento\Store\Model\Website;
use Magento\Store\Model\Store;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndex;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Store\Model\Group;

$objectManager = Bootstrap::getObjectManager();
//Creating second website with a store.
/** @var $website \Magento\Store\Model\Website */
$website = $objectManager->create(Website::class);
$website->load('test', 'code');

if (!$website->getId()) {
    $website->setData([
        'code' => 'test',
        'name' => 'Test Website',
        'is_default' => '0',
    ]);
    $website->save();
}

/**
 * @var Group $storeGroup
 */
$storeGroup = $objectManager->create(Group::class);
$storeGroup->setCode('some_group')
    ->setName('custom store group')
    ->setWebsite($website);
$storeGroup->save($storeGroup);

$website->setDefaultGroupId($storeGroup->getId());
$website->save($website);

$websiteId = $website->getId();
$store = $objectManager->create(Store::class);
$store->load('fixture_second_store', 'code');

if (!$store->getId()) {
    $groupId = $website->getDefaultGroupId();
    $store->setData([
        'code' => 'fixture_second_store',
        'website_id' => $websiteId,
        'group_id' => $groupId,
        'name' => 'Fixture Second Store',
        'sort_order' => 10,
        'is_active' => 1,
    ]);
    $store->save();
}

//Setting up allowed countries
$configResource = $objectManager->get(\Magento\Config\Model\ResourceModel\Config::class);
//Allowed countries for default website.
$configResource->saveConfig(
    'general/country/allow',
    'FR',
    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
    1
);
//Allowed countries for second website
$configResource->saveConfig(
    'general/country/allow',
    'ES,US,UK,DE',
    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,
    $websiteId
);

/* Refresh CatalogSearch index */
/** @var \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry */
$indexerRegistry = $objectManager->create(\Magento\Framework\Indexer\IndexerRegistry::class);
$indexerRegistry->get(FulltextIndex::INDEXER_ID)->reindexAll();
//Clear config cache.
$objectManager->get(ReinitableConfigInterface::class)->reinit();
