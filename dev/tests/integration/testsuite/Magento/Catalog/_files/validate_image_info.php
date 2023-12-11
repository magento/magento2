<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Filesystem $filesystem */
$filesystem = $objectManager->get(\Magento\Framework\Filesystem::class);

/** @var Magento\Catalog\Model\Product\Media\Config $config */
$config = $objectManager->get(\Magento\Catalog\Model\Product\Media\Config::class);

/** @var $mediaDirectory \Magento\Framework\Filesystem\Directory\WriteInterface */
$mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
$mediaDirectory->create($config->getBaseTmpMediaPath());

$targetTmpFilePath = $mediaDirectory->getAbsolutePath($config->getBaseTmpMediaPath() . '/magento_small_image.jpg');
$mediaDirectory->getDriver()->filePutContents($targetTmpFilePath, file_get_contents(__DIR__ . '/magento_small_image.jpg'));
// Copying the image to target dir is not necessary because during product save, it will be moved there from tmp dir
