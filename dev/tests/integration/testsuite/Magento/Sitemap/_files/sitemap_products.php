<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// Copy images to tmp media path
use Magento\Framework\App\Filesystem\DirectoryList;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Catalog\Model\Product\Media\Config $config */
$config = $objectManager->get('Magento\Catalog\Model\Product\Media\Config');

/** @var \Magento\Framework\Filesystem $filesystem */
$filesystem = $objectManager->get('Magento\Framework\Filesystem');
/** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
$mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
$mediaPath = $mediaDirectory->getAbsolutePath();
$baseTmpMediaPath = $config->getBaseTmpMediaPath();
$mediaDirectory->create($baseTmpMediaPath);

copy(__DIR__ . '/magento_image_sitemap.png', $mediaPath . '/' . $baseTmpMediaPath . '/magento_image_sitemap.png');
copy(__DIR__ . '/second_image.png', $mediaPath . '/' . $baseTmpMediaPath . '/second_image.png');

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setId(
    1
)->setAttributeSetId(
    4
)->setName(
    'Simple Product Enabled'
)->setSku(
    'simple_no_images'
)->setPrice(
    10
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setWebsiteIds(
    [1]
)->setStockData(
    ['qty' => 100, 'is_in_stock' => 1]
)->save();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setId(
    2
)->setAttributeSetId(
    4
)->setName(
    'Simple Product Invisible'
)->setSku(
    'simple_invisible'
)->setPrice(
    10
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setWebsiteIds(
    [1]
)->setStockData(
    ['qty' => 100, 'is_in_stock' => 1]
)->setRelatedLinkData(
    [1 => ['position' => 1]]
)->save();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setId(
    3
)->setAttributeSetId(
    4
)->setName(
    'Simple Product Disabled'
)->setSku(
    'simple_disabled'
)->setPrice(
    10
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED
)->setWebsiteIds(
    [1]
)->setStockData(
    ['qty' => 100, 'is_in_stock' => 1]
)->setRelatedLinkData(
    [1 => ['position' => 1]]
)->save();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setId(
    4
)->setAttributeSetId(
    4
)->setName(
    'Simple Images'
)->setSku(
    'simple_with_images'
)->setPrice(
    10
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setImage(
    '/s/e/second_image.png'
)->setSmallImage(
    '/m/a/magento_image_sitemap.png'
)->setThumbnail(
    '/m/a/magento_image_sitemap.png'
)->addImageToMediaGallery(
    $mediaPath . '/' . $baseTmpMediaPath . '/magento_image_sitemap.png',
    null,
    false,
    false
)->addImageToMediaGallery(
    $mediaPath . '/' . $baseTmpMediaPath . '/second_image.png',
    null,
    false,
    false
)->setWebsiteIds(
    [1]
)->setStockData(
    ['qty' => 100, 'is_in_stock' => 1]
)->setRelatedLinkData(
    [1 => ['position' => 1]]
)->save();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setId(
    5
)->setAttributeSetId(
    4
)->setName(
    'Simple Images'
)->setSku(
    'simple_with_images'
)->setPrice(
    10
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->setImage(
    'no_selection'
)->setSmallImage(
    '/m/a/magento_image_sitemap.png'
)->setThumbnail(
    'no_selection'
)->addImageToMediaGallery(
    $baseTmpMediaPath . '/second_image.png',
    null,
    false,
    false
)->setWebsiteIds(
    [1]
)->setStockData(
    ['qty' => 100, 'is_in_stock' => 1]
)->setRelatedLinkData(
    [1 => ['position' => 1]]
)->save();
