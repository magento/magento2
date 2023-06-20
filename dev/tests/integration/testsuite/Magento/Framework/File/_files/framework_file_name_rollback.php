<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\Filesystem\DirectoryList;

$fileSystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Framework\Filesystem::class
);
/** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
$mediaDirectory = $fileSystem->getDirectoryWrite(DirectoryList::MEDIA);
/** @var \Magento\Framework\Filesystem\Directory\WriteInterface $varDirectory */
$varDirectory = $fileSystem->getDirectoryWrite(DirectoryList::VAR_DIR);

$mediaDirectory->delete('image');
$varDirectory->delete('image');
