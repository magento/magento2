<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Filesystem $filesystem */
$filesystem = $objectManager->get('Magento\Framework\Filesystem');

/** @var Magento\Catalog\Model\Product\Media\Config $config */
$config = $objectManager->get('Magento\Catalog\Model\Product\Media\Config');

/** @var $tmpDirectory \Magento\Framework\Filesystem\Directory\WriteInterface */
$tmpDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
$tmpDirectory->create($config->getBaseTmpMediaPath());

$targetTmpFilePath = $tmpDirectory->getAbsolutePath($config->getBaseTmpMediaPath() . '/magento_small_image.jpg');
copy(__DIR__ . '/magento_small_image.jpg', $targetTmpFilePath);
// Copying the image to target dir is not necessary because during product save, it will be moved there from tmp dir
