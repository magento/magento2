<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

$skus = ['SKU-1', 'SKU-2', 'SKU-3'];

foreach ($skus as $sku) {
    $product = $productRepository->get($sku);

    $stockItem = $product->getExtensionAttributes()->getStockItem();
    $stockItem->setUseConfigManageStock(false);
    $stockItem->setManageStock(true);
    $stockItem = $product->getExtensionAttributes()->setStockItem($stockItem);

    $productRepository->save($product);
}
