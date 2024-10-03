<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Category;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var WriteInterface $mediaDirectory */
$mediaDirectory = $objectManager->get(\Magento\Framework\Filesystem::class)->getDirectoryWrite(DirectoryList::MEDIA);
$fileName = 'magento_small_image.jpg';
$filePath = 'catalog/category/' . $fileName;
$mediaDirectory->create('catalog/category');
$shortImageContent = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . $fileName);
$mediaDirectory->getDriver()->filePutContents($mediaDirectory->getAbsolutePath($filePath), $shortImageContent);


$filePath = 'catalog/category/magento_small_image.jpg';
/** @var Category $category */
$categoryParent = $objectManager->create(Category::class);
$categoryParent->setName('Parent Image Category')
    ->setPath('1/2')
    ->setLevel(2)
    ->setImage($filePath)
    ->setAvailableSortBy('name')
    ->setDefaultSortBy('name')
    ->setIsActive(true)
    ->setPosition(1)
    ->save();

$mediaDirectory->getDriver()->deleteFile($mediaDirectory->getAbsolutePath($filePath));
