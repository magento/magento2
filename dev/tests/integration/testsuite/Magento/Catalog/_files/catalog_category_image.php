<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\Filesystem\DirectoryList;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var $mediaDirectory \Magento\Framework\Filesystem\Directory\WriteInterface */
$mediaDirectory = $objectManager->get(\Magento\Framework\Filesystem::class)->getDirectoryWrite(DirectoryList::MEDIA);
$fileName = 'magento_small_image.jpg';
$fileNameLong = 'magento_long_image_name_magento_long_image_name_magento_long_image_name.jpg';
$filePath = 'catalog/category/' . $fileName;
$filePathLong = 'catalog/category/' . $fileNameLong;
$mediaDirectory->create('catalog/category');
$shortImageContent = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $fileName);
$longImageContent = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $fileNameLong);
$mediaDirectory->getDriver()->filePutContents($mediaDirectory->getAbsolutePath($filePath), $shortImageContent);
$mediaDirectory->getDriver()->filePutContents($mediaDirectory->getAbsolutePath($filePathLong), $longImageContent);
unset($shortImageContent, $longImageContent);
