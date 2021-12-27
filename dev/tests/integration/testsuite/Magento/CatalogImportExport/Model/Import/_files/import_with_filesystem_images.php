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

$items = [
    [
        'source' => __DIR__ . '/../../../../../Magento/Catalog/_files/magento_image.jpg',
        'dest' => '/magento_image.jpg',
    ],
    [
        'source' => __DIR__ . '/../../../../../Magento/Catalog/_files/magento_small_image.jpg',
        'dest' => '/magento_small_image.jpg',
    ],
    [
        'source' => __DIR__ . '/../../../../../Magento/Catalog/_files/magento_thumbnail.jpg',
        'dest' => '/magento_thumbnail.jpg',
    ],
    [
        'source' => __DIR__ . '/magento_additional_image_one.jpg',
        'dest' => '/magento_additional_image_one.jpg',
    ],
    [
        'source' => __DIR__ . '/magento_additional_image_two.jpg',
        'dest' => '/magento_additional_image_two.jpg',
    ],
];

foreach ($items as $item) {
    $driver = $mediaDirectory->getDriver();
    $driver->filePutContents(
        $mediaDirectory->getAbsolutePath($path . $item['dest']),
        file_get_contents($item['source'])
    );
}
