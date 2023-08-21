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
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Bundle\Model\PrepareBundleLinks;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/multiple_products.php');

$objectManager = Bootstrap::getObjectManager();
/** @var PrepareBundleLinks $prepareBundleLinks */
$prepareBundleLinks = $objectManager->get(PrepareBundleLinks::class);
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
$defaultWebsiteId = $storeManager->getWebsite('base')->getId();
/** @var ProductInterfaceFactory $productFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var Magento\Catalog\Api\Data\ProductInterface $bundleProduct */
$bundleProduct = $productFactory->create();
$bundleProduct->setTypeId(Type::TYPE_BUNDLE)
    ->setAttributeSetId($bundleProduct->getDefaultAttributeSetId())
    ->setWebsiteIds([$defaultWebsiteId])
    ->setName('Bundle Product With Dynamic Price')
    ->setSku('bundle_product_with_dynamic_price')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(
        [
            'use_config_manage_stock' => 0,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ]
    )
    ->setSkuType(0)
    ->setPriceView(0)
    ->setPriceType(Price::PRICE_TYPE_DYNAMIC)
    ->setPrice(null)
    ->setWeightType(0)
    ->setShipmentType(AbstractType::SHIPMENT_TOGETHER);

$bundleOptionsData = [
    [
        'title' => 'Option 1',
        'default_title' => 'Option 1',
        'type' => 'select',
        'required' => 1,
    ],
    [
        'title' => 'Option 2',
        'default_title' => 'Option 2',
        'type' => 'select',
        'required' => 1,
    ],
];
$bundleSelectionsData = [
    [
        [
            'sku' => 'simple1',
            'selection_qty' => 1,
        ],
    ],
    [
        [
            'sku' => 'simple2',
            'selection_qty' => 1,
        ],
    ]
];
$bundleProduct = $prepareBundleLinks->execute($bundleProduct, $bundleOptionsData, $bundleSelectionsData);
$productRepository->save($bundleProduct);
