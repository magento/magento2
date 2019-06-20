<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$registry = $objectManager->get(Registry::class);
$stockStatusRepository = $objectManager->create(StockStatusRepositoryInterface::class);
$stockStatusCriteriaFactory = $objectManager->create(StockStatusCriteriaInterfaceFactory::class);

$searchCriteriaBuilder = Bootstrap::getObjectManager()->get(SearchCriteriaBuilder::class);
$searchCriteria = $searchCriteriaBuilder->addFilter(
    ProductInterface::SKU,
    ['sku1', 'sku2', 'sku3', 'sku4'],
    'in'
)->create();
$products = $productRepository->getList($searchCriteria)->getItems();

/**
 * Tests which are wrapped with MySQL transaction clear all data by transaction rollback.
 * In that case there is "if" which checks that "sku1", "sku2", "sku3" and "sku4" still exists in database.
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
