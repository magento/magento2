<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\Filesystem\DirectoryList;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$targetDirectory = $objectManager->get(\Magento\Framework\Filesystem\Directory\TargetDirectory::class);
/** @var $mediaDirectory \Magento\Framework\Filesystem\Directory\WriteInterface */
$mediaDirectory = $targetDirectory->getDirectoryWrite(DirectoryList::ROOT);

$filesToCopy = [
    'empty.png',
    'image_adapters_test.png',
    'magento_thumbnail.jpg',
    'notanimage.txt',
    'watermark.gif',
    'watermark.jpg',
    'watermark.png',
    'watermark_alpha.png',
    'watermark_alpha_base_image.jpg',
];

foreach ($filesToCopy as $fileName) {
    $subDir = 'image/test/';
    $filePath =  $subDir . $fileName;
    if (!$mediaDirectory->isExist($filePath)) {
        $mediaDirectory->create($subDir);
        $mediaDirectory->getDriver()->filePutContents(
            $mediaDirectory->getAbsolutePath($filePath),
            file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $fileName)
        );
    }
}
