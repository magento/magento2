<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\TestFramework\Bundle\Model\PrepareBundleLinks;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Bundle/_files/multiple_products.php');

$objectManager = Bootstrap::getObjectManager();
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$prepareBundleLinks = $objectManager->get(PrepareBundleLinks::class);
$product = $objectManager->create(Product::class);
$product->setTypeId(Type::TYPE_BUNDLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Bundle Product')
    ->setSku('bundle_product')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setPriceView(0)
    ->setPriceType(Price::PRICE_TYPE_FIXED)
    ->setPrice(110.0)
    ->setShipmentType(0);

$optionData = [
    [
        'title' => 'Test Option',
        'default_title' => 'Test Option',
        'type' => 'radio',
        'required' => 1,
        'delete' => '',
    ],
];
$selectionData = [
    [
        'sku' => 'simple1',
        'selection_qty' => 1,
        'selection_price_value' => 0,
        'selection_price_type' => 1,
        'selection_can_change_qty' => 1,
    ],
];
$product = $prepareBundleLinks->execute($product, $optionData, [$selectionData]);
$productRepository->save($product);
