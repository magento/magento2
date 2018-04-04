<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\Data\ProductInterface;

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
/** @var StockStatusRepositoryInterface $stockStatusRepository */
$stockStatusRepository = $objectManager->create(StockStatusRepositoryInterface::class);
/** @var StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory */
$stockStatusCriteriaFactory = $objectManager->create(StockStatusCriteriaInterfaceFactory::class);

/** @var SearchCriteriaBuilder $searchCriteriaBuilder */
$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder->addFilter(
    ProductInterface::SKU,
    ['SKU-1', 'SKU-2', 'SKU-3', 'SKU-4'],
    'in'
)->create();
$products = $productRepository->getList($searchCriteria)->getItems();

/**
 * Tests which are wrapped with MySQL transaction clear all data by transaction rollback.
 * In that case there is "if" which checks that SKU1, SKU2 and SKU3 still exists in database.
 */
if (!empty($products)) {
    $currentArea = $registry->registry('isSecureArea');
    $registry->unregister('isSecureArea');
    $registry->register('isSecureArea', true);

    foreach ($products as $product) {
        $criteria = $stockStatusCriteriaFactory->create();
        $criteria->setProductsFilter($product->getId());

        $result = $stockStatusRepository->getList($criteria);
        if ($result->getTotalCount()) {
            $stockStatus = current($result->getItems());
            $stockStatusRepository->delete($stockStatus);
        }

        $productRepository->delete($product);
    }

    $registry->unregister('isSecureArea');
    $registry->register('isSecureArea', $currentArea);
}
