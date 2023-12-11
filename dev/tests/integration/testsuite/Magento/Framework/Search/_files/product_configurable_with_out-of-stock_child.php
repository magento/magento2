<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\DefaultCategory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Framework/Search/_files/product_configurable.php');

$objectManager = Bootstrap::getObjectManager();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);

$product = $productRepository->get('simple_1010');
$product->setStockData(
    [
        'qty' => 0,
    ]
);
$productRepository->save($product);

$product = $productRepository->get('simple_1020');
$product->setStockData(
    [
        'qty' => 0,
    ]
);
$productRepository->save($product);

/** @var CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->create(CategoryLinkManagementInterface::class);
/** @var DefaultCategory $categoryHelper */
$categoryHelper = $objectManager->get(DefaultCategory::class);

foreach (['simple_1010', 'simple_1020', 'configurable'] as $sku) {
    $categoryLinkManagement->assignProductToCategories($sku, [$categoryHelper->getId(), 333]);
}
