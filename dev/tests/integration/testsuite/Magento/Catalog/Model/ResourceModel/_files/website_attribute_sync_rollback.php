<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use \Magento\Framework\App\ObjectManager;
use \Magento\Store\Api\StoreRepositoryInterface;
use \Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use \Magento\Framework\Registry;
use \Magento\TestFramework\Helper\Bootstrap;
use \Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Roll back fixtures
 *  - Remove Product
 *  - Remove Website/StoreGroup/[Store1, Store2]
 *  - ReIndex Full text indexers
 */

$productId = 333;
$objectManager = Bootstrap::getObjectManager();
$storeRepository = $objectManager->get(StoreRepositoryInterface::class);
$resourceConnection = $objectManager->get(ResourceConnection::class);
/**
 * @var AdapterInterface $connection
 */
$connection = $resourceConnection->getConnection();
$registry = $objectManager->get(Registry::class);
$productRepository = $objectManager->get(ProductRepositoryInterface::class);


/**
 * Marks area as secure so Product repository would allow product removal
 */
$isSecuredAreaSystemState = $registry->registry('isSecuredArea');
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/**
 * @var \Magento\Store\Model\Store $store
 */
$store = $storeRepository->get('customstoreview1');
$storeGroupId = $store->getStoreGroupId();
$websiteId = $store->getWebsiteId();

try {
    $product = $productRepository->getById($productId);
    if ($product->getId()) {
        $productRepository->delete($product);
    }
} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
    //Product already removed
}

/**
 * Remove stores by code
 */
$storeCodes = [
    'customstoreview1',
    'customstoreview2',
];

$connection->delete(
    $resourceConnection->getTableName('store'),
    [
        'code IN (?)' => $storeCodes,
    ]
);

/**
 * removeStoreGroupById
 */
$connection->delete(
    $resourceConnection->getTableName('store_group'),
    [
        'group_id = ?' => $storeGroupId,
    ]
);

/**
 * remove website by id
 */
$connection->delete(
    $resourceConnection->getTableName('store_website'),
    [
        'website_id = ?' => $websiteId,
    ]
);

/**
 * reIndex all
 */
ObjectManager::getInstance()
    ->create(\Magento\CatalogSearch\Model\Indexer\Fulltext\Processor::class)
    ->reindexAll();

/**
 * Revert mark area secured
 */
$registry->unregister('isSecuredArea');
$registry->register('isSecuredArea', $isSecuredAreaSystemState);
