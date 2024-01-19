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
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple_with_custom_file_option.php');
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();
$product = $productRepository->get('simple_with_custom_file_option');
$option = [
    'title' => 'file option 2',
    'type' => 'file',
    'is_require' => true,
    'sort_order' => 1,
    'price' => 20.0,
    'price_type' => 'percent',
    'sku' => 'sku4',
    'file_extension' => 'jpg, png, gif',
    'image_size_x' => 1000,
    'image_size_y' => 1000,
];
$customOptions = $product->getOptions();
/** @var ProductCustomOptionInterfaceFactory $customOptionFactory */
$customOptionFactory = $objectManager->create(ProductCustomOptionInterfaceFactory::class);
/** @var ProductCustomOptionInterface $customOption */
$customOption = $customOptionFactory->create(['data' => $option]);
$customOption->setProductSku($product->getSku());
$customOptions[] = $customOption;
$product->setOptions($customOptions);
$productRepository->save($product);
