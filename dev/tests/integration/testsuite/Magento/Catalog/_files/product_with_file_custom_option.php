<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);

$product->setTypeId('simple')
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Simple Product With File Custom Option')
    ->setSku('simple-with-file-option')
    ->setPrice(10)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setCanSaveCustomOptions(true)
    ->setStockData(
        [
            'qty' => 100,
            'is_in_stock' => 1,
        ]
    )
    ->setHasOptions(true);

$option = [
    'title' => 'file option',
    'type' => 'file',
    'is_require' => true,
    'sort_order' => 3,
    'price' => 30.0,
    'price_type' => 'fixed',
    'sku' => 'file option sku',
    'file_extension' => 'jpg,png,gif',
    'image_size_x' => 2000,
    'image_size_y' => 2000,
];

$customOptions = [];

/** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $customOptionFactory */
$customOptionFactory = $objectManager->create(\Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory::class);

/** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterface $customOption */
$customOption = $customOptionFactory->create(['data' => $option]);
$customOption->setProductSku($product->getSku());
$customOptions[] = $customOption;

$product->setOptions($customOptions);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryFactory */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$productRepository->save($product);
