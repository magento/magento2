<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type as Simple;
use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Model\Product\Type as Downloadable;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var  ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var ProductInterfaceFactory $productFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);

$productTypes = [
    Bundle::TYPE_CODE => ['price_view' => 1, 'price_type' => Price::PRICE_TYPE_FIXED],
    Configurable::TYPE_CODE => [],
    Downloadable::TYPE_DOWNLOADABLE => [],
    Grouped::TYPE_CODE => [],
    Simple::TYPE_SIMPLE => [],
    Simple::TYPE_VIRTUAL => [],
];

foreach ($productTypes as $productType => $additionalProductData) {
    $attrProductData = [
        'attribute_set_id' => 4,
        'type_id' => $productType,
        'name' => $productType . '_name',
        'sku' => $productType . '_sku',
        'price' => 10,
        'visibility' => Visibility::VISIBILITY_BOTH,
        'status' => Status::STATUS_ENABLED,
    ];

    if (!empty($additionalProductData)) {
        $attrProductData = array_merge($attrProductData, $additionalProductData);
    }

    /** @var $product ProductInterface */
    $product = $productFactory->create(['data' => $attrProductData]);
    $productRepository->save($product);
}
