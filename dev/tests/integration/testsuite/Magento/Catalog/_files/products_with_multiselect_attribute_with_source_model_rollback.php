<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture(
    'Magento/Catalog/_files/multiselect_attribute_with_source_model_rollback.php'
);

/**
 * Remove all products as strategy of isolation process
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$registry = $objectManager->get('Magento\Framework\Registry');
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
$searchCriteriaBuilder->addFilter(ProductInterface::SKU, 'simple_mssm_%', 'like');

/** @var ProductSearchResultsInterface $products */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$products = $productRepository->getList($searchCriteriaBuilder->create());
/** @var ProductInterface $product */
foreach ($products->getItems() as $product) {
    $productRepository->delete($product);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(IndexerRegistry::class)
    ->get(Magento\CatalogInventory\Model\Indexer\Stock\Processor::INDEXER_ID)
    ->reindexAll();
