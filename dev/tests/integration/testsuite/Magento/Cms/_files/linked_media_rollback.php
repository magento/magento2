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
$pubDir = $filesystem->getDirectoryWrite(Magento\Framework\App\Filesystem\DirectoryList::PUB);
$fullDirectoryPath = $pubDir->getAbsolutePath() . DIRECTORY_SEPARATOR . $directoryName;
$mediaDirectory = $filesystem->getDirectoryWrite(Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
$wysiwygDir = $mediaDirectory->getAbsolutePath() . 'wysiwyg';
if (is_link($wysiwygDir)) {
    unlink($wysiwygDir);
}
if (is_dir($fullDirectoryPath)) {
    $pubDir->delete($directoryName);
}
