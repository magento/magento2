<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Magento\MediaGalleryApi\Api\SaveAssetsInterface;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var AssetInterfaceFactory $mediaAssetFactory */
$mediaAssetFactory = $objectManager->get(AssetInterfaceFactory::class);
/** @var AssetInterface $mediaAsset */
$mediaAsset = $mediaAssetFactory->create(
    [
        'path' => 'testDirectory/year_ago_loaded_img.jpg',
        'description' => 'Description of an image',
        'contentType' => 'image',
        'title' => 'Img',
        'source' => 'Local',
        'width' => 420,
        'height' => 240,
        'size' => 12877,
        'createdAt' => (new \DateTime('-1 year'))->format('Y-m-d H:i:s'),
    ]
);
/** @var SaveAssetsInterface $mediaSave */
$mediaSave = $objectManager->get(SaveAssetsInterface::class);
$mediaId = $mediaSave->execute([$mediaAsset]);
