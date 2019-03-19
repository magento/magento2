<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();

/** @var Product $product */
$product = $objectManager->create(Product::class);
$product->isObjectNew(true);
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product Decimal Qty')
    ->setSku('simple_with_decimal_qty')
    ->setPrice(10)
    ->setWeight(1)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED);

/** @var StockItemInterface $stockItem */
$stockItem = $objectManager->create(StockItemInterface::class);
$stockItem->setIsInStock(true)
    ->setQty(10000)
    ->setIsQtyDecimal(true)
    ->setUseConfigMinSaleQty(false)
    ->setMinSaleQty(1.1)
    ->setUseConfigEnableQtyInc(false)
    ->setEnableQtyIncrements(true)
    ->setUseConfigQtyIncrements(false)
    ->setQtyIncrements(1.1);

$extensionAttributes = $product->getExtensionAttributes();
$extensionAttributes->setStockItem($stockItem);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$product = $productRepository->save($product);
