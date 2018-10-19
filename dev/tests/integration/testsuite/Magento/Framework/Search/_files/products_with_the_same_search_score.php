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
use Magento\TestFramework\Helper\Bootstrap;

Bootstrap::getInstance()->reinitialize();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()
    ->create(ProductRepositoryInterface::class);

$howManyProducts = 5;

foreach (range(1, $howManyProducts) as $productId) {
    $product = Bootstrap::getObjectManager()->create(Product::class);
    $product->setTypeId(Type::TYPE_SIMPLE)
        ->setAttributeSetId(4)
        ->setWebsiteIds([1])
        ->setName('Cool short' . $productId)
        ->setSku('cool_shorts_' . $productId)
        ->setPrice(42)
        ->setShortDescription("Some description about shorts")
        ->setTaxClassId(0)
        ->setDescription('Some description about <b>shorts</b>')
        ->setVisibility(Visibility::VISIBILITY_BOTH)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(
            [
                'use_config_manage_stock' => 1,
                'qty' => 100,
                'is_qty_decimal' => 0,
                'is_in_stock' => 1,
            ]
        );

    $productRepository->save($product);
}
