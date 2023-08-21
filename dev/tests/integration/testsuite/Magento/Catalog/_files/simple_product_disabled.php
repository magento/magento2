<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\DefaultCategory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var WebsiteRepositoryInterface $websiteRepository */
$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$defaultWebsiteId = $websiteRepository->get('base')->getId();
/** @var DefaultCategory $defaultCategory */
$defaultCategory = $objectManager->get(DefaultCategory::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var ProductInterfaceFactory $productFactory */
$productFactory = $objectManager->get(ProductInterfaceFactory::class);
$product = $productFactory->create();
$productData = [
    ProductInterface::TYPE_ID => Type::TYPE_SIMPLE,
    ProductInterface::ATTRIBUTE_SET_ID => $product->getDefaultAttributeSetId(),
    ProductInterface::SKU => 'product_disabled',
    ProductInterface::NAME => 'Product with category',
    ProductInterface::PRICE => 10,
    ProductInterface::VISIBILITY => Visibility::VISIBILITY_BOTH,
    ProductInterface::STATUS => Status::STATUS_DISABLED,
    'website_ids' => [$defaultWebsiteId],
    'stock_data' => [
        'use_config_manage_stock' => 1,
        'qty' => 100,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
    ],
    'category_ids' => [$defaultCategory->getId()],
];
$product->setData($productData);

$productRepository->save($product);
