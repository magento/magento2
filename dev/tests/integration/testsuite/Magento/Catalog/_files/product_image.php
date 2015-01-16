<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\Filesystem\DirectoryList;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $mediaConfig \Magento\Catalog\Model\Product\Media\Config */
$mediaConfig = $objectManager->get('Magento\Catalog\Model\Product\Media\Config');

/** @var $mediaDirectory \Magento\Framework\Filesystem\Directory\WriteInterface */
$mediaDirectory = $objectManager->get('Magento\Framework\Filesystem')
    ->getDirectoryWrite(DirectoryList::MEDIA);
$targetDirPath = $mediaConfig->getBaseMediaPath() . str_replace('/', DIRECTORY_SEPARATOR, '/m/a/');
$targetTmpDirPath = $mediaConfig->getBaseTmpMediaPath() . str_replace('/', DIRECTORY_SEPARATOR, '/m/a/');
$mediaDirectory->create($targetDirPath);
$mediaDirectory->create($targetTmpDirPath);

$targetTmpFilePath = $mediaDirectory->getAbsolutePath() . DIRECTORY_SEPARATOR . $targetTmpDirPath
    . DIRECTORY_SEPARATOR . 'magento_image.jpg';
copy(__DIR__ . '/magento_image.jpg', $targetTmpFilePath);
// Copying the image to target dir is not necessary because during product save, it will be moved there from tmp dir
