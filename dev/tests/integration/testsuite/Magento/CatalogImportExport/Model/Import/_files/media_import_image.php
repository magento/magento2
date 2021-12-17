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
$mediaDirectory->create('import/m/a');
$dirPath = $mediaDirectory->getAbsolutePath('import/m/a');
$driver = $mediaDirectory->getDriver();
$driver->createDirectory($dirPath);
$driver->filePutContents(
    $dirPath . '/magento_image.jpg',
    file_get_contents(__DIR__ . '/../../../../../Magento/Catalog/_files/magento_image.jpg')
);
