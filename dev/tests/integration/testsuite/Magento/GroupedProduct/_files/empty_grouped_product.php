<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$product = Bootstrap::getObjectManager()->create(Product::class);
$product->isObjectNew(true);
$product->setTypeId(Grouped::TYPE_CODE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Grouped Product')
    ->setSku('grouped-product')
    ->setPrice(100)
    ->setTaxClassId(0)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 0, 'is_in_stock' => 1])
    ->save();
