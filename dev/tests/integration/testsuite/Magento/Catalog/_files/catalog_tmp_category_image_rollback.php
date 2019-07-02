<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Framework\App\Filesystem\DirectoryList;

/** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
$mediaDirectory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Framework\Filesystem::class
)->getDirectoryWrite(
    DirectoryList::MEDIA
);
/** @var $imageUploader \Magento\Catalog\Model\ImageUploader */
$imageUploader = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Model\ImageUploader::class,
    [
        'baseTmpPath' => $mediaDirectory->getRelativePath('catalog/tmp/category'),
        'basePath' => $mediaDirectory->getRelativePath('catalog/category'),
        'allowedExtensions' => ['jpg', 'jpeg', 'gif', 'png'],
        'allowedMimeTypes' => ['image/jpg', 'image/jpeg', 'image/gif', 'image/png']
    ]
);

$mediaDirectory->delete($imageUploader->getBaseTmpPath());
