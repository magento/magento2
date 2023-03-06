<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\Filesystem\DirectoryList;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $mediaConfig \Magento\Catalog\Model\Product\Media\Config */
$mediaConfig = $objectManager->get(\Magento\Catalog\Model\Product\Media\Config::class);
/** @var $database \Magento\MediaStorage\Helper\File\Storage\Database */
$database = $objectManager->get(\Magento\MediaStorage\Helper\File\Storage\Database::class);

/** @var $mediaDirectory \Magento\Framework\Filesystem\Directory\WriteInterface */
$mediaDirectory = $objectManager->get(\Magento\Framework\Filesystem::class)
    ->getDirectoryWrite(DirectoryList::MEDIA);
$targetDirPath = $mediaConfig->getBaseMediaPath() . str_replace('/', DIRECTORY_SEPARATOR, '/m/a/');
$targetTmpDirPath = $mediaConfig->getBaseTmpMediaPath() . str_replace('/', DIRECTORY_SEPARATOR, '/m/a/');
$mediaDirectory->create($targetDirPath);
$mediaDirectory->create($targetTmpDirPath);

$images = ['magento_image.jpg', 'magento_small_image.jpg', 'magento_thumbnail.jpg'];

foreach ($images as $image) {
    $targetTmpFilePath = $mediaDirectory->getAbsolutePath() . $targetTmpDirPath . $image;

    $sourceFilePath = __DIR__ . DIRECTORY_SEPARATOR . $image;
    $mediaDirectory->getDriver()->filePutContents($targetTmpFilePath, file_get_contents($sourceFilePath));

    // Copying the image to target dir is not necessary because during product save, it will be moved there from tmp dir
    $database->saveFile($targetTmpFilePath);
}
