<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\Product\Visibility;
use Magento\TestFramework\Bundle\Model\PrepareBundleLinks;

require __DIR__ . '/../../../Magento/Catalog/_files/category_with_different_price_products.php';

/** @var PrepareBundleLinks $prepareBundleLinks */
$prepareBundleLinks = $objectManager->get(PrepareBundleLinks::class);
$defaultWebsiteId = $storeManager->getWebsite('base')->getId();

$bundleProduct = $productFactory->create();
$bundleProduct->setTypeId(Type::TYPE_BUNDLE)
    ->setAttributeSetId($bundleProduct->getDefaultAttributeSetId())
    ->setWebsiteIds([$defaultWebsiteId])
    ->setName('Bundle Product')
    ->setSku('dynamic_bundle_product_with_special_price')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(
        [
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ]
    )
    ->setSkuType(0)
    ->setPriceView(0)
    ->setPriceType(Price::PRICE_TYPE_DYNAMIC)
    ->setPrice(null)
    ->setSpecialPrice(75)
    ->setWeightType(0)
    ->setShipmentType(AbstractType::SHIPMENT_TOGETHER);

$bundleOptionsData = [
    [
        'title' => 'Option 1',
        'default_title' => 'Option 1',
        'type' => 'select',
        'required' => 1,
    ],
];
$bundleSelectionsData = [
    [
        [
            'sku' => $product->getSku(),
            'selection_qty' => 1,
            'selection_price_value' => 0,
            'selection_can_change_qty' => 1,
        ],
        [
            'sku' => $product2->getSku(),
            'selection_qty' => 1,
            'selection_price_value' => 0,
            'selection_can_change_qty' => 1,
        ],
    ]
];
$bundleProduct = $prepareBundleLinks->execute($bundleProduct, $bundleOptionsData, $bundleSelectionsData);
$productRepository->save($bundleProduct);
