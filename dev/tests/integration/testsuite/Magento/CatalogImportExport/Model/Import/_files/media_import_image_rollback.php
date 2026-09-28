<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

/** @var \Magento\Framework\Filesystem\Directory\Write $mediaDirectory */
$mediaDirectory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Framework\Filesystem::class
)->getDirectoryWrite(
    \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
);
$mediaDirectory->delete('import');
$mediaDirectory->delete('catalog');
