<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Filesystem\Directory\WriteInterface;

$objectManager = Bootstrap::getObjectManager();

/** @var \Magento\Framework\Filesystem $filesystem */
$filesystem = $objectManager->get(Filesystem::class);

/** @var Magento\Catalog\Model\Product\Media\Config $config */
$config = $objectManager->get(Config::class);

/** @var $mediaDirectory WriteInterface */
$mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
$mediaDirectory->create($config->getBaseTmpMediaPath());

$targetTmpFilePath = $mediaDirectory->getAbsolutePath($config->getBaseTmpMediaPath() . '/magento_small_image.svg');
$mediaDirectory->getDriver()->filePutContents(
    $targetTmpFilePath,
    file_get_contents(__DIR__ . '/magento_small_image.jpg')
);
// Copying the image to target dir is not necessary because during product save, it will be moved there from tmp dir
