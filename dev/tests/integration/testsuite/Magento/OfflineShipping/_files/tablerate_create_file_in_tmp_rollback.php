<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Filesystem\Directory\WriteInterface;

$objectManager = Bootstrap::getObjectManager();
/** @var Filesystem $fileSystem */
$fileSystem = $objectManager->get(Filesystem::class);
/** @var WriteInterface $tmpDirectory */
$tmpDirectory = $fileSystem->getDirectoryWrite(DirectoryList::SYS_TMP);
$fileName = 'tablerates.csv';

$tmpDirectory->delete($fileName);
