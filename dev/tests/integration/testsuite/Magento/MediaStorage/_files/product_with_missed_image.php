<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

use Magento\Framework\App\Filesystem\DirectoryList;

require dirname(__DIR__, 2) . '/Catalog/_files/product_image.php';
require dirname(__DIR__, 2) . '/Catalog/_files/product_simple.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$product = $productRepository->get('simple');
$product->setStoreId(0)
    ->setImage('/m/a/magento_image.jpg')
    ->setSmallImage('/m/a/magento_image.jpg')
    ->setThumbnail('/m/a/magento_image.jpg')
    ->setData(
        'media_gallery',
        [
            'images' => [
                [
                    'file' => '/m/a/magento_image.jpg',
                    'position' => 1,
                    'label' => 'Image Alt Text',
                    'disabled' => 0,
                    'media_type' => 'image',
                ],
            ],
        ]
    )->save();
$image = array_shift($product->getData('media_gallery')['images']);
$product = $productRepository->get('simple', false, 1, true);
$product->setData(
    'media_gallery',
    [
        'images' => [
            [
                'value_id' => $image['value_id'],
                'file' => $image['file'],
                'disabled' => 1,
                'media_type' => 'image',
            ],
        ],
    ]
);
$productRepository->save($product);

$mediaDirectory = $objectManager->get(\Magento\Framework\Filesystem::class)
    ->getDirectoryWrite(DirectoryList::MEDIA);

$config = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Catalog\Model\Product\Media\Config::class
);

$mediaDirectory->delete($config->getBaseMediaPath() . '/m/a/magento_image.jpg');
