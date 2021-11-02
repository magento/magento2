<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$directoryName = 'linked_media';
/** @var \Magento\Framework\Filesystem $filesystem */
$filesystem = $objectManager->get(\Magento\Framework\Filesystem::class);

$targetDirectory = $objectManager->get(\Magento\Framework\Filesystem\Directory\TargetDirectory::class)
    ->getDirectoryWrite(Magento\Framework\App\Filesystem\DirectoryList::ROOT);
$fullDirectoryPath = $targetDirectory->getAbsolutePath('pub/' . $directoryName);

$mediaDirectory = $filesystem->getDirectoryWrite(Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
$wysiwygDir = $mediaDirectory->getAbsolutePath() . 'wysiwyg';
$mediaDirectory->delete($wysiwygDir);

$targetDirectory->create($fullDirectoryPath);
$targetDirectory->createSymlink($fullDirectoryPath, $wysiwygDir);
