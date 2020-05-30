<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_image.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_simple.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$product = $productRepository->get('simple');

/** @var $product \Magento\Catalog\Model\Product */
$product->setStoreId(0)
    ->setImage('/m/a/magento_image.jpg')
    ->setSmallImage('/m/a/magento_image.jpg')
    ->setThumbnail('/m/a/magento_thumbnail.jpg')
    ->setSwatchImage('/m/a/magento_thumbnail.jpg')
    ->setData('media_gallery', ['images' => [
        [
            'file' => '/m/a/magento_image.jpg',
            'position' => 1,
            'label' => 'Image Alt Text',
            'disabled' => 0,
            'media_type' => 'image'
        ],
        [
            'file' => '/m/a/magento_thumbnail.jpg',
            'position' => 2,
            'label' => 'Thumbnail Image',
            'disabled' => 0,
            'media_type' => 'image'
        ],
    ]])
    ->setCanSaveCustomOptions(true)
    ->save();
