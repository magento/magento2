<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_image.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple.php');

$objectManager = Bootstrap::getObjectManager();
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('simple');
$imageData = [
    'file' => '/m/a/magento_image.jpg',
    'position' => 1,
    'label' => 'Image Alt Text',
    'disabled' => 0,
    'media_type' => 'image'
];

/** @var $product Product */
$product->setStoreId(0)
    ->setData('media_gallery', ['images' => [$imageData]])
    ->setCanSaveCustomOptions(true)
    ->save();
