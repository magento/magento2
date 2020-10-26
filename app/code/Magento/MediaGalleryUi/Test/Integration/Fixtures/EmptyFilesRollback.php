<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Test\Integration\Fixtures;

use Magento\Framework\Filesystem\Driver\File;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

$objectManager = Bootstrap::getObjectManager();
$file = $objectManager->get(File::class);
$filesystem = $objectManager->get(Filesystem::class);

$mediaPath = $filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();

$file->deleteDirectory($mediaPath . '/fixturefolder');
