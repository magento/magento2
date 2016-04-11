<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// Copy images to tmp media path

use Magento\Framework\App\Filesystem\DirectoryList;

$obectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$obectManager->get(
    'Magento\Framework\View\DesignInterface'
)->setArea(
    'frontend'
)->setDefaultDesignTheme();

/** @var \Magento\Catalog\Model\Product\Media\Config $config */
$config = $obectManager->get('Magento\Catalog\Model\Product\Media\Config');
/** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
$mediaDirectory = $obectManager->get('Magento\Framework\Filesystem')
    ->getDirectoryWrite(DirectoryList::MEDIA);

$baseTmpMediaPath = $config->getBaseTmpMediaPath();
$mediaDirectory->create($baseTmpMediaPath);
copy(__DIR__ . '/product_image.png', $mediaDirectory->getAbsolutePath($baseTmpMediaPath . '/product_image.png'));

/** @var $productOne \Magento\Catalog\Model\Product */
$productOne = $obectManager->create('Magento\Catalog\Model\Product');
$productOne->setId(
    1
)->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setAttributeSetId(
    4
)->setWebsiteIds(
    [$obectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getWebsiteId()]
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
    ['use_config_manage_stock' => 1, 'qty' => 99, 'is_in_stock' => 1]
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
$productTwo = $obectManager->create('Magento\Catalog\Model\Product');
$productTwo->setId(
    2
)->setTypeId(
    \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
)->setAttributeSetId(
    4
)->setWebsiteIds(
    [$obectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getWebsiteId()]
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
