<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\Filesystem\Directory\Write $mediaDirectory */
$mediaDirectory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Framework\Filesystem::class
)->getDirectoryWrite(
    \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
);

$path = 'catalog' . DIRECTORY_SEPARATOR . 'product';
// Is required for using importDataForMediaTest method.
$mediaDirectory->create('import');
$importMediaPath = $mediaDirectory->getAbsolutePath('import');
$mediaDirectory->create($path);
$productMediaPath = $mediaDirectory->getAbsolutePath($path);

$items = [
    [
        'source' => __DIR__ . '/../../../../../Magento/Catalog/_files/magento_image.jpg',
        'dest' => $productMediaPath . '/magento_image.jpg',
    ],
    [
        'source' => __DIR__ . '/../../../../../Magento/Catalog/_files/magento_image_2.jpg',
        'dest' => $productMediaPath . '/magento_image_2.jpg',
    ],
    [
        'source' => __DIR__ . '/../../../../../Magento/Catalog/_files/magento_small_image.jpg',
        'dest' => $productMediaPath . '/magento_small_image.jpg',
    ],
    [
        'source' => __DIR__ . '/../../../../../Magento/Catalog/_files/magento_small_image.jpg',
        'dest' => $productMediaPath . '/magento_small_image_2.jpg',
    ],
    [
        'source' => __DIR__ . '/../../../../../Magento/Catalog/_files/magento_thumbnail.jpg',
        'dest' => $productMediaPath . '/magento_thumbnail.jpg',
    ],
    [
        'source' => __DIR__ . '/magento_additional_image_one.jpg',
        'dest' => $productMediaPath . '/magento_additional_image_one.jpg',
    ],
    [
        'source' => __DIR__ . '/magento_additional_image_two.jpg',
        'dest' => $productMediaPath . '/magento_additional_image_two.jpg',
    ],
    [
        'source' => __DIR__ . '/../../../../../Magento/Catalog/_files/magento_image.jpg',
        'dest' => $importMediaPath . '/magento_image.jpg',
    ],
    [
        'source' => __DIR__ . '/../../../../../Magento/Catalog/_files/magento_image_2.jpg',
        'dest' => $importMediaPath . '/magento_image_2.jpg',
    ],
    [
        'source' => __DIR__ . '/../../../../../Magento/Catalog/_files/magento_small_image.jpg',
        'dest' => $importMediaPath . '/magento_small_image.jpg',
    ],
    [
        'source' => __DIR__ . '/../../../../../Magento/Catalog/_files/magento_small_image.jpg',
        'dest' => $importMediaPath . '/magento_small_image_2.jpg',
    ],
    [
        'source' => __DIR__ . '/../../../../../Magento/Catalog/_files/magento_thumbnail.jpg',
        'dest' => $importMediaPath . '/magento_thumbnail.jpg',
    ],
    [
        'source' => __DIR__ . '/magento_additional_image_one.jpg',
        'dest' => $importMediaPath . '/magento_additional_image_one.jpg',
    ],
    [
        'source' => __DIR__ . '/magento_additional_image_two.jpg',
        'dest' => $importMediaPath . '/magento_additional_image_two.jpg',
    ],
];

foreach ($items as $item) {
    copy($item['source'], $item['dest']);
}
