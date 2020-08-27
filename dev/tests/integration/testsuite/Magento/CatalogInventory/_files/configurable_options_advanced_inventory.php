<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/product_configurable.php');

$objectManager = Bootstrap::getObjectManager();

/** @var StockItemRepositoryInterface $stockItemRepository */
$stockItemRepository = $objectManager->get(StockItemRepositoryInterface::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
/** @var ProductInterface $product */
$product = $productRepository->get('simple_10');

/** @var StockItemInterface $stockItem */
$stockItem = $product->getExtensionAttributes()->getStockItem();
$stockItem->setIsInStock(true)
    ->setQty(10000)
    ->setUseConfigMinSaleQty(false)
    ->setMinSaleQty(500)
    ->setUseConfigEnableQtyInc(false)
    ->setEnableQtyIncrements(true)
    ->setUseConfigQtyIncrements(false)
    ->setQtyIncrements(500);

$stockItemRepository->save($stockItem);
