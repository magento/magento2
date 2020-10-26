<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Test\Integration\Fixtures;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;

$emptyFilesCount = 50000;

$objectManager = Bootstrap::getObjectManager();

$file = $objectManager->get(File::class);
$filesystem = $objectManager->get(Filesystem::class);

$filenamePattern = 'mediGalleryEmptyTxtFile.txt';
$mediaPath = $filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();

$file->createDirectory($mediaPath . '/fixturefolder');

for ($i = 0; $i < $emptyFilesCount; $i++) {
    $file->filePutContents($mediaPath . '/fixturefolder/' . $i . $filenamePattern, '');
}
