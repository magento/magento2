<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronization\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Magento\MediaGalleryApi\Api\GetAssetsByPathsInterface;
use Magento\MediaGallerySynchronizationApi\Model\CreateAssetFromFileInterface;

/**
 * Create media asset object based on the file information
 */
class GetAssetFromPath
{
    /**
     * @var GetAssetsByPathsInterface
     */
    private $getAssetsByPaths;

    /**
     * @var AssetInterfaceFactory
     */
    private $assetFactory;

    /**
     * @var CreateAssetFromFileInterface
     */
    private $createAssetFromFile;

    /**
     * @param AssetInterfaceFactory $assetFactory
     * @param GetAssetsByPathsInterface $getMediaGalleryAssetByPath
     * @param CreateAssetFromFileInterface $createAssetFromFile
     */
    public function __construct(
        AssetInterfaceFactory $assetFactory,
        GetAssetsByPathsInterface $getMediaGalleryAssetByPath,
        CreateAssetFromFileInterface $createAssetFromFile
    ) {
        $this->assetFactory = $assetFactory;
        $this->getAssetsByPaths = $getMediaGalleryAssetByPath;
        $this->createAssetFromFile = $createAssetFromFile;
    }

    /**
     * Create media asset object based on the file information
     *
     * @param string $path
     * @return AssetInterface
     * @throws LocalizedException
     * @throws ValidatorException
     */
    public function execute(string $path): AssetInterface
    {
        $asset = $this->getAsset($path);
        $assetFromFile = $this->createAssetFromFile->execute($path);

        if (!$asset) {
            return $assetFromFile;
        }

        return $this->assetFactory->create(
            [
                'id' => $asset->getId(),
                'path' => $path,
                'title' => $asset->getTitle(),
                'description' => $asset->getDescription() ?? $assetFromFile->getDescription(),
                'width' => $assetFromFile->getWidth(),
                'height' => $assetFromFile->getHeight(),
                'hash' => $assetFromFile->getHash(),
                'size' => $assetFromFile->getSize(),
                'contentType' => $asset->getContentType(),
                'source' => $asset->getSource()
            ]
        );
    }

    /**
     * Returns asset if asset already exist by provided path
     *
     * @param string $path
     * @return AssetInterface|null
     * @throws ValidatorException
     * @throws LocalizedException
     */
    private function getAsset(string $path): ?AssetInterface
    {
        $asset = $this->getAssetsByPaths->execute([$path]);
        return !empty($asset) ? $asset[0] : null;
    }
}
