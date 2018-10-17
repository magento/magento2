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
    ->setName('Simple Product 20')
    ->setSku('simple_20')
    ->setPrice(20)
    ->setWeight(1)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED);

/** @var StockItemInterface $stockItem */
$stockItem = $objectManager->create(StockItemInterface::class);
$stockItem->setQty(100)
    ->setIsInStock(true);
$extensionAttributes = $product->getExtensionAttributes();
$extensionAttributes->setStockItem($stockItem);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$product = $productRepository->save($product);
