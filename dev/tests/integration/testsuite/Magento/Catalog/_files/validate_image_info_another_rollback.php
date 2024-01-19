<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var \Magento\Framework\Filesystem $filesystem */
$filesystem = $objectManager->create(Filesystem::class);

/** @var Config $config */
$config = $objectManager->get(Config::class);

/** @var $tmpDirectory WriteInterface */
$tmpDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);

$targetTmpFilePath = $tmpDirectory->getAbsolutePath($config->getBaseTmpMediaPath() . '/magento_small_image.svg');
if (file_exists($targetTmpFilePath)) {
    unlink($targetTmpFilePath);
}
