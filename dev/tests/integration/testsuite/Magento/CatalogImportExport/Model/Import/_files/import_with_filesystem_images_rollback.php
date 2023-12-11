<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var \Magento\Framework\Filesystem $fileSystem */
$fileSystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Framework\Filesystem::class
);
/** @var \Magento\Framework\Filesystem\Directory\Write $mediaDirectory */
$mediaDirectory = $fileSystem->getDirectoryWrite(
    \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
);
/** @var \Magento\Framework\Filesystem\Directory\Write $varDirectory */
$varDirectory = $fileSystem->getDirectoryWrite(
    \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR
);
$varDirectory->delete('import');
$mediaDirectory->delete('catalog');
