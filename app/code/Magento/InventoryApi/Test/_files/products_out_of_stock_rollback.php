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
    ['SKU-4', 'SKU-5', 'SKU-6'],
    'in'
)->create();
$products = $productRepository->getList($searchCriteria)->getItems();

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
