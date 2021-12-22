<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// Copy images to tmp media path

use Magento\Framework\App\Filesystem\DirectoryList;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$objectManager->get(
    \Magento\Framework\View\DesignInterface::class
)->setArea(
    'frontend'
)->setDefaultDesignTheme();

/** @var \Magento\Catalog\Model\Product\Media\Config $config */
$config = $objectManager->get(\Magento\Catalog\Model\Product\Media\Config::class);
/** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
$mediaDirectory = $objectManager->get(\Magento\Framework\Filesystem::class)
    ->getDirectoryWrite(DirectoryList::MEDIA);

$baseTmpMediaPath = $config->getBaseTmpMediaPath();
$mediaDirectory->create($baseTmpMediaPath);
$mediaDirectory->getDriver()->filePutContents($mediaDirectory->getAbsolutePath($baseTmpMediaPath . '/product_image.png'), file_get_contents(__DIR__ . '/product_image.png'));

/** @var $productOne \Magento\Catalog\Model\Product */
$productOne = $objectManager->create(\Magento\Catalog\Model\Product::class);
$productOne->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setAttributeSetId(
    4
)->setWebsiteIds(
    [$objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore()->getWebsiteId()]
)->setSku(
    'simple_product_1'
)->setName(
    'Simple Product 1 Name'
)->setDescription(
    'Simple Product 1 Full Description'
)->setShortDescription(
    'Simple Product 1 Short Description'
)->setPrice(
    1234.56
)->setTaxClassId(
    2
)->setStockData(
    [
        'use_config_manage_stock'   => 1,
        'qty'                       => 99,
        'is_qty_decimal'            => 0,
        'is_in_stock'               => 1,
    ]
)->setMetaTitle(
    'Simple Product 1 Meta Title'
)->setMetaKeyword(
    'Simple Product 1 Meta Keyword'
)->setMetaDescription(
    'Simple Product 1 Meta Description'
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->addImageToMediaGallery(
    $baseTmpMediaPath . '/product_image.png',
    null,
    false,
    false
)->save();

/** @var $productTwo \Magento\Catalog\Model\Product */
$productTwo = $objectManager->create(\Magento\Catalog\Model\Product::class);
$productTwo->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setAttributeSetId(
    4
)->setWebsiteIds(
    [$objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore()->getWebsiteId()]
)->setSku(
    'simple_product_2'
)->setName(
    'Simple Product 2 Name'
)->setDescription(
    'Simple Product 2 Full Description'
)->setShortDescription(
    'Simple Product 2 Short Description'
)->setPrice(
    987.65
)->setTaxClassId(
    2
)->setStockData(
    ['use_config_manage_stock' => 1, 'qty' => 24, 'is_in_stock' => 1]
)->setVisibility(
    \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
)->setStatus(
    \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
)->save();
