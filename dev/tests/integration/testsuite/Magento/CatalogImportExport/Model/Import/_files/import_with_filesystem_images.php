<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\Filesystem $fileSystem */
$fileSystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Framework\Filesystem::class
);
/** @var \Magento\Framework\Filesystem\Directory\Write $mediaDirectory */
$mediaDirectory = $fileSystem->getDirectoryWrite(
    \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
);
/** @var \Magento\Framework\Filesystem\Directory\Write $varDirectory */
$varDirectory = $fileSystem->getDirectoryWrite(
    \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR
);

$path = 'catalog' . DIRECTORY_SEPARATOR . 'product';
$varImagesPath = 'import' . DIRECTORY_SEPARATOR . 'images';
// Is required for using importDataForMediaTest method.
$varDirectory->create($varImagesPath);
$mediaDirectory->create($path);
$dirPath = $mediaDirectory->getAbsolutePath($path);

$items = [
    [
        'source' => __DIR__ . '/../../../../../Magento/Catalog/_files/magento_image.jpg',
        'dest' => $dirPath . '/magento_image.jpg',
    ],
    [
        'source' => __DIR__ . '/../../../../../Magento/Catalog/_files/magento_small_image.jpg',
        'dest' => $dirPath . '/magento_small_image.jpg',
    ],
    [
        'source' => __DIR__ . '/../../../../../Magento/Catalog/_files/magento_thumbnail.jpg',
        'dest' => $dirPath . '/magento_thumbnail.jpg',
    ],
    [
        'source' => __DIR__ . '/magento_additional_image_one.jpg',
        'dest' => $dirPath . '/magento_additional_image_one.jpg',
    ],
    [
        'source' => __DIR__ . '/magento_additional_image_two.jpg',
        'dest' => $dirPath . '/magento_additional_image_two.jpg',
    ],
];

foreach ($items as $item) {
    copy($item['source'], $item['dest']);
}
