<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronizationMetadata\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Magento\MediaGalleryMetadataApi\Api\ExtractMetadataInterface;
use Magento\MediaGallerySynchronizationApi\Model\CreateAssetFromFileInterface;

/**
 * Add metadata to the asset created from file
 */
class CreateAssetFromFileMetadata
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var AssetInterfaceFactory
     */
    private $assetFactory;

    /**
     * @var ExtractMetadataInterface
     */
    private $extractMetadata;

    /**
     * @param Filesystem $filesystem
     * @param AssetInterfaceFactory $assetFactory
     * @param ExtractMetadataInterface $extractMetadata
     */
    public function __construct(
        Filesystem $filesystem,
        AssetInterfaceFactory $assetFactory,
        ExtractMetadataInterface $extractMetadata
    ) {
        $this->filesystem = $filesystem;
        $this->assetFactory = $assetFactory;
        $this->extractMetadata = $extractMetadata;
    }

    /**
     * Add metadata to the asset
     *
     * @param CreateAssetFromFileInterface $subject
     * @param AssetInterface $asset
     * @return AssetInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(CreateAssetFromFileInterface $subject, AssetInterface $asset): AssetInterface
    {
        $metadata = $this->extractMetadata->execute(
            $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($asset->getPath())
        );

        return $this->assetFactory->create(
            [
                'id' => $asset->getId(),
                'path' => $asset->getPath(),
                'title' => $metadata->getTitle() ?: $asset->getTitle(),
                'description' => $metadata->getDescription(),
                'width' => $asset->getWidth(),
                'height' => $asset->getHeight(),
                'hash' => $asset->getHash(),
                'size' => $asset->getSize(),
                'contentType' => $asset->getContentType(),
                'source' => $asset->getSource()
            ]
        );
    }
}
