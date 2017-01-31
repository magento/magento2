<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/product_image.php';
require __DIR__ . '/product_simple.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
$product = $productRepository->get('simple');

/** @var $product \Magento\Catalog\Model\Product */
$product->setStoreId(0)
    ->setImage('/m/a/magento_image.jpg')
    ->setSmallImage('/m/a/magento_image.jpg')
    ->setThumbnail('/m/a/magento_image.jpg')
    ->setData('media_gallery', ['images' => [
        [
            'file' => '/m/a/magento_image.jpg',
            'position' => 1,
            'label' => 'Image Alt Text',
            'disabled' => 0,
            'media_type' => 'image'
        ],
    ]])
    ->setCanSaveCustomOptions(true)
    ->save();
