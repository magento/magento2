<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$directoryName = 'linked_media';
/** @var \Magento\Framework\Filesystem $filesystem */
$filesystem = $objectManager->get(\Magento\Framework\Filesystem::class);
$fullDirectoryPath = $filesystem->getDirectoryRead(Magento\Framework\App\Filesystem\DirectoryList::PUB)
        ->getAbsolutePath() . $directoryName;
$mediaDirectory = $filesystem->getDirectoryWrite(Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

$wysiwygDir = $mediaDirectory->getAbsolutePath() . 'wysiwyg';
$mediaDirectory->delete($wysiwygDir);
if (!is_dir($fullDirectoryPath)) {
    mkdir($fullDirectoryPath);
}
if (is_dir($fullDirectoryPath) && !is_dir($wysiwygDir)) {
    symlink($fullDirectoryPath, $wysiwygDir);
}
