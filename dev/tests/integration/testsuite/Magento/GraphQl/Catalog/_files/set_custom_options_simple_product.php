<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

$productCustomOptions = [];
$optionsSet = [
    [
        'title' => 'test_option_code_1',
        'type' => 'field',
        'is_require' => false,
        'sort_order' => 1,
        'price' => -10.0,
        'price_type' => 'fixed',
        'sku' => 'sku1',
        'max_characters' => 100,
    ],
    [
        'title' => 'area option',
        'type' => 'area',
        'is_require' => false,
        'sort_order' => 2,
        'price' => 20.0,
        'price_type' => 'percent',
        'sku' => 'sku2',
        'max_characters' => 100
    ]
];

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$product = $productRepository->get('simple_product');
/** @var ProductCustomOptionInterfaceFactory $customOptionFactory */
$customOptionFactory = $objectManager->get(ProductCustomOptionInterfaceFactory::class);

foreach ($optionsSet as $option) {
    /** @var ProductCustomOptionInterface $customOption */
    $customOption = $customOptionFactory->create(['data' => $option]);
    $customOption->setProductSku($product->getSku());

    $productCustomOptions[] = $customOption;
}

$product->setOptions($productCustomOptions);
$productRepository->save($product);
