<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\AdobeStockAssetApi\Model\Asset\Command\DeleteByIdInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var AssetInterfaceFactory $mediaAssetFactory */
$mediaAssetFactory = $objectManager->get(AssetInterfaceFactory::class);
/** @var AssetInterface $mediaAsset */
$mediaAsset = $mediaAssetFactory->create(
    [
        'data' => [
            'id' => 55,
            'path' => 'testDirectory/path.jpg'
        ]
    ]
);
/** @var DeleteByIdInterface $deleteMediaAsset */
$deleteMediaAsset = $objectManager->get(DeleteByIdInterface::class);
$deleteMediaAsset->execute(55);