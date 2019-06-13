<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../../Magento/ConfigurableProduct/_files/product_configurable.php';

$objectManager = Bootstrap::getObjectManager();

/** @var StockItemRepositoryInterface $stockItemRepository */
$stockItemRepository = $objectManager->get(StockItemRepositoryInterface::class);

/** @var ProductInterface $product */
$product = $productRepository->get('simple_10');

/** @var StockItemInterface $stockItem */
$stockItem = $product->getExtensionAttributes()->getStockItem();
$stockItem->setIsInStock(true)
    ->setQty(1);
$stockItemRepository->save($stockItem);

/** @var ProductInterface $product */
$product = $productRepository->get('simple_20');

/** @var StockItemInterface $stockItem */
$stockItem = $product->getExtensionAttributes()->getStockItem();
$stockItem->setIsInStock(false)
    ->setQty(0);
$stockItemRepository->save($stockItem);

