<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Bundle\Model\PrepareBundleLinks;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../../Magento/Catalog/_files/product_simple_duplicated.php';
require __DIR__ . '/../../../Magento/Catalog/_files/second_product_simple.php';

$objectManager = Bootstrap::getObjectManager();
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$baseWebsiteId = $websiteRepository->get('base')->getId();
/** @var PrepareBundleLinks $prepareBundleLinks */
$prepareBundleLinks = $objectManager->get(PrepareBundleLinks::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();
/** @var ProductFactory $productFactory */
$productFactory = $objectManager->get(ProductFactory::class);
$bundleProduct = $productFactory->create();
$bundleProduct->setTypeId(Type::TYPE_BUNDLE)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([$baseWebsiteId])
    ->setName('Bundle Product Checkbox Required Options')
    ->setSku('bundle-product-checkbox-required-options')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setPriceView(0)
    ->setSkuType(1)
    ->setWeightType(1)
    ->setPriceType(Price::PRICE_TYPE_DYNAMIC)
    ->setPrice(10.0)
    ->setShipmentType(AbstractType::SHIPMENT_TOGETHER);

$bundleOptionsData = [
    [
        'title' => 'Checkbox Options',
        'default_title' => 'Checkbox Options',
        'type' => 'checkbox',
        'required' => 1,
        'delete' => '',
    ],
];
$bundleSelectionsData = [
    [
        'sku' => $product->getSku(),
        'selection_qty' => 1,
        'selection_price_value' => 0,
        'selection_can_change_qty' => 1,
        'delete' => '',
    ],
    [
        'sku' => $product2->getSku(),
        'selection_qty' => 1,
        'selection_price_value' => 0,
        'selection_can_change_qty' => 1,
        'delete' => '',
    ],
];

$bundleProduct = $prepareBundleLinks->execute($bundleProduct, $bundleOptionsData, [$bundleSelectionsData]);
$productRepository->save($bundleProduct);
