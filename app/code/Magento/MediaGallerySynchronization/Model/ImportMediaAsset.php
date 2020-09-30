<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Model;

use Magento\MediaGalleryApi\Api\SaveAssetsInterface;
use Magento\MediaGallerySynchronizationApi\Model\ImportFilesInterface;

/**
 * Import image file to the media gallery asset table
 */
class ImportMediaAsset implements ImportFilesInterface
{
    /**
     * @var SaveAssetsInterface
     */
    private $saveAssets;

    /**
     * @var GetAssetFromPath
     */
    private $getAssetFromPath;

    /**
     * @param SaveAssetsInterface $saveAssets
     * @param GetAssetFromPath $getAssetFromPath
     */
    public function __construct(
        SaveAssetsInterface $saveAssets,
        GetAssetFromPath $getAssetFromPath
    ) {
        $this->saveAssets = $saveAssets;
        $this->getAssetFromPath = $getAssetFromPath;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $paths): void
    {
        $assets = [];

        foreach ($paths as $path) {
            $assets[] = $this->getAssetFromPath->execute($path);
        }

        $this->saveAssets->execute($assets);
    }
}
