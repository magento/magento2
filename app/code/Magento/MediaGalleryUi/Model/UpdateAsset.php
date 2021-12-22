<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Magento\MediaGalleryApi\Api\GetAssetsByIdsInterface;
use Magento\MediaGalleryApi\Api\SaveAssetsInterface;
use Magento\MediaGalleryMetadataApi\Api\Data\MetadataInterface;
use Magento\MediaGalleryUi\Model\UpdateAsset\UpdateKeywords;
use Magento\MediaGalleryUi\Model\UpdateAsset\SaveMetadataToFile;

class UpdateAsset
{
    /**
     * @var AssetInterfaceFactory
     */
    private $assetFactory;

    /**
     * @var GetAssetsByIdsInterface
     */
    private $getAssetsByIds;

    /**
     * @var SaveAssetsInterface
     */
    private $saveAssets;

    /**
     * @var SaveMetadataToFile
     */
    private $processMetadata;

    /**
     * @var UpdateKeywords
     */
    private $processKeywords;

    /**
     * @param AssetInterfaceFactory $assetFactory
     * @param GetAssetsByIdsInterface $getAssetsByIds
     * @param SaveAssetsInterface $saveAssets
     * @param UpdateKeywords $processKeywords
     * @param SaveMetadataToFile $processMetadata
     */
    public function __construct(
        AssetInterfaceFactory $assetFactory,
        GetAssetsByIdsInterface $getAssetsByIds,
        SaveAssetsInterface $saveAssets,
        UpdateKeywords $processKeywords,
        SaveMetadataToFile $processMetadata
    ) {
        $this->assetFactory = $assetFactory;
        $this->getAssetsByIds = $getAssetsByIds;
        $this->saveAssets = $saveAssets;
        $this->processKeywords = $processKeywords;
        $this->processMetadata = $processMetadata;
    }

    /**
     * Save asset details
     *
     * @param int $id
     * @param MetadataInterface $data
     */
    public function execute(int $id, MetadataInterface $data): void
    {
        $asset = $this->getAsset($id);

        $updatedAsset = $this->assetFactory->create(
            [
                'id' => $asset->getId(),
                'path' => $asset->getPath(),
                'title' => $data->getTitle() ?? $asset->getTitle(),
                'description' => $data->getDescription() ?? $asset->getDescription(),
                'width' => $asset->getWidth(),
                'height' => $asset->getHeight(),
                'size' => $asset->getSize(),
                'hash' => $asset->getHash(),
                'contentType' => $asset->getContentType(),
                'source' => $asset->getSource()
            ]
        );

        $this->saveAssets->execute([$updatedAsset]);
        $this->processMetadata->execute($asset->getPath(), $data);

        $keywords = $data->getKeywords();
        if (isset($keywords)) {
            $this->processKeywords->execute($id, $keywords);
        }
    }

    /**
     * Load asset by id
     *
     * @param int $id
     * @return AssetInterface
     * @throws LocalizedException
     */
    private function getAsset(int $id): AssetInterface
    {
        $assets = $this->getAssetsByIds->execute([$id]);
        if (empty($assets)) {
            throw new LocalizedException(__('Could not retrieve the asset.'));
        }
        return current($assets);
    }
}
