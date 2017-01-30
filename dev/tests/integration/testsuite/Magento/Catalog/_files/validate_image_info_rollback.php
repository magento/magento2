<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Filesystem $filesystem */
$filesystem = $objectManager->create('Magento\Framework\Filesystem');

/** @var Magento\Catalog\Model\Product\Media\Config $config */
$config = $objectManager->get('Magento\Catalog\Model\Product\Media\Config');

/** @var $tmpDirectory \Magento\Framework\Filesystem\Directory\WriteInterface */
$tmpDirectory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

$targetTmpFilePath = $tmpDirectory->getAbsolutePath($config->getBaseTmpMediaPath() . '/magento_small_image.jpg');
if (file_exists($targetTmpFilePath)) {
    unlink($targetTmpFilePath);
}
