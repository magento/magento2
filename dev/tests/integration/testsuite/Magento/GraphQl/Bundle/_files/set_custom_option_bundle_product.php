<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$options = [
    [
        'title' => 'test_option_code_1',
        'type' => 'field',
        'is_require' => false,
        'sort_order' => 1,
        'price' => 10.0,
        'price_type' => 'fixed',
        'sku' => 'sku1',
        'max_characters' => 10,
    ]
];

/** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $customOptionFactory */
$customOptionFactory = $objectManager->create(\Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();
$bundleProduct = $productRepository->get('bundle-product-two-dropdown-options');
$customOptions = [];

foreach ($options as $option) {
    /** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterface $customOption */
    $customOption = $customOptionFactory->create(['data' => $option]);
    $customOption->setProductSku($bundleProduct->getSku());

    $customOptions[] = $customOption;
}

$bundleProduct->setOptions($customOptions);
$productRepository->save($bundleProduct);
