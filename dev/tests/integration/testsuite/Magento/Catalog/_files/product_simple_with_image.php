<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\Data\ProductExtensionInterfaceFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->isObjectNew(true);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId(1)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

/** @var $mediaConfig \Magento\Catalog\Model\Product\Media\Config */
$mediaConfig = $objectManager->get(\Magento\Catalog\Model\Product\Media\Config::class);

/** @var $mediaDirectory \Magento\Framework\Filesystem\Directory\WriteInterface */
$mediaDirectory = $objectManager->get(\Magento\Framework\Filesystem::class)
    ->getDirectoryWrite(DirectoryList::MEDIA);

$targetDirPath = $mediaConfig->getBaseMediaPath();
$targetTmpDirPath = $mediaConfig->getBaseTmpMediaPath();

$mediaDirectory->create($targetDirPath);
$mediaDirectory->create($targetTmpDirPath);

$dist = $mediaDirectory->getAbsolutePath($mediaConfig->getBaseMediaPath() .  DIRECTORY_SEPARATOR . 'magento_image.jpg');
$mediaDirectory->getDriver()->filePutContents($dist, file_get_contents(__DIR__ . '/magento_image.jpg'));

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$productRepository->save($product);
