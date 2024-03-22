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
/** @var $varDirectory \Magento\Framework\Filesystem\Directory\WriteInterface */
$varDirectory = $objectManager->get(\Magento\Framework\Filesystem::class)->getDirectoryWrite(DirectoryList::VAR_DIR);
$dir = 'image';
$mediaDirectory->create($dir);
$imageContent = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'magento_small_image.jpg');

$mediaDriver = $mediaDirectory->getDriver();
$mediaDriver->filePutContents($mediaDirectory->getAbsolutePath($dir . '/image_one.jpg'), $imageContent);
$mediaDriver->filePutContents($mediaDirectory->getAbsolutePath($dir . '/image_two.jpg'), $imageContent);
$mediaDriver->filePutContents($mediaDirectory->getAbsolutePath($dir . '/image_two_1.jpg'), $imageContent);

$varDirectory->create($dir);
$varDirectory->getDriver()->filePutContents($varDirectory->getAbsolutePath($dir . '/image_one.jpg'), $imageContent);

unset($imageContent);
