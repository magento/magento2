<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Customer\Model\Group;
use Magento\TestFramework\Bundle\Model\PrepareBundleLinks;

require __DIR__ . '/multiple_products.php';

/** @var PrepareBundleLinks $prepareBundleLinks */
$prepareBundleLinks = $objectManager->get(PrepareBundleLinks::class);
/** @var ProductTierPriceInterfaceFactory $tierPriceFactory */
$tierPriceFactory = $objectManager->get(ProductTierPriceInterfaceFactory::class);
/** @var  $tierPriceExtensionAttributesFactory */
$tierPriceExtensionAttributesFactory = $objectManager->get(ProductTierPriceExtensionFactory::class);

$bundleProduct = $productFactory->create();
$bundleProduct->setTypeId(Type::TYPE_BUNDLE)
    ->setAttributeSetId($bundleProduct->getDefaultAttributeSetId())
    ->setWebsiteIds([$defaultWebsiteId])
    ->setName('Bundle Product')
    ->setSku('fixed_bundle_product_with_tier_price')
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
    ->setPriceView(1)
    ->setSkuType(1)
    ->setWeightType(1)
    ->setPriceType(Price::PRICE_TYPE_FIXED)
    ->setPrice(50.0)
    ->setShipmentType(AbstractType::SHIPMENT_TOGETHER);

$bundleOptionsData = [
    [
        'title' => 'Option 1',
        'default_title' => 'Option 1',
        'type' => 'radio',
        'required' => 1,
        'delete' => '',
    ],
];
$bundleSelectionsData = [
    [
        'sku' => $product->getSku(),
        'selection_qty' => 1,
        'selection_price_value' => 10,
        'selection_price_type' => 0,
        'selection_can_change_qty' => 1,
    ],
    [
        'sku' => $product2->getSku(),
        'selection_qty' => 1,
        'selection_price_value' => 25,
        'selection_price_type' => 1,
        'selection_can_change_qty' => 1,
    ],
    [
        'sku' => $product3->getSku(),
        'selection_qty' => 1,
        'selection_price_value' => 25,
        'selection_price_type' => 0,
        'selection_can_change_qty' => 1,
    ],
];
$bundleProduct = $prepareBundleLinks->execute($bundleProduct, $bundleOptionsData, [$bundleSelectionsData]);

$tierPriceExtensionAttribute = $tierPriceExtensionAttributesFactory->create(
    [
        'data' => [
            'website_id' => 0,
            'percentage_value' => 25,
        ]
    ]
);
$tierPrices[] = $tierPriceFactory->create(
    [
        'data' => [
            'customer_group_id' => Group::CUST_GROUP_ALL,
            'qty' => 1,
        ]
    ]
)->setExtensionAttributes($tierPriceExtensionAttribute);
$bundleProduct->setTierPrices($tierPrices);
$productRepository->save($bundleProduct);
