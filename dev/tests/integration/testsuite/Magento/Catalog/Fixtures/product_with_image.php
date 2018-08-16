<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

require __DIR__ . '/../_files/product_image.php';
require __DIR__ . '/../_files/product_simple.php';

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('simple');

/** @var Product $product */
$product->setStoreId(1)
    ->setImage('/m/a/magento_image1.jpg')
    ->setSmallImage('/m/a/magento_image1.jpg')
    ->setThumbnail('/m/a/magento_image1.jpg')
    ->setData(
        'media_gallery',
        [
            'images' => [
                [
                    'file' => '/m/a/magento_image.jpg',
                    'position' => 1,
                    'label' => 'Image Alt Text 1',
                    'disabled' => 0,
                    'media_type' => 'image'
                ],
            ]
        ]
    )
    ->setCanSaveCustomOptions(true)
    ->save();
