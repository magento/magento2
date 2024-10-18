<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Magento\MediaGalleryApi\Api\GetAssetsByPathsInterface;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

/**
 * Get media assets by paths
 */
class GetAssetsByPaths implements GetAssetsByPathsInterface
{
    private const TABLE_MEDIA_GALLERY_ASSET = 'media_gallery_asset';
    private const MEDIA_GALLERY_ASSET_PATH = 'path';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AssetInterfaceFactory
     */
    private $mediaAssetFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GetByPath constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param AssetInterfaceFactory $mediaAssetFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        AssetInterfaceFactory $mediaAssetFactory,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->mediaAssetFactory = $mediaAssetFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $paths): array
    {
        $assets = [];
        try {
            foreach ($this->getAssetsData($paths) as $assetData) {
                $assets[] = $this->mediaAssetFactory->create(
                    [
                        'id' => $assetData['id'],
                        'path' => $assetData['path'],
                        'title' => $assetData['title'],
                        'description' => $assetData['description'],
                        'source' => $assetData['source'],
                        'hash' => $assetData['hash'],
                        'contentType' => $assetData['content_type'],
                        'width' => $assetData['width'],
                        'height' => $assetData['height'],
                        'size' => $assetData['size'],
                        'createdAt' => $assetData['created_at'],
                        'updatedAt' => $assetData['updated_at'],
                    ]
                );
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new LocalizedException(
                __(
                    'Could not get media assets for paths: %paths',
                    [
                        'paths' => implode(' ,', $paths)
                    ]
                )
            );
        }
        return $assets;
    }

    /**
     * Retrieve assets data from database
     *
     * @param array $paths
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    private function getAssetsData(array $paths): array
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($this->resourceConnection->getTableName(self::TABLE_MEDIA_GALLERY_ASSET))
            ->where(self::MEDIA_GALLERY_ASSET_PATH . ' IN (?)', $paths);
        $assets = $connection->query($select)->fetchAll();

        return $this->filterCaseSensitiveAssets($assets, $paths);
    }

    /**
     * Filter out assets with mixed case that doesn't match the paths
     *
     * @param array $assets
     * @param array $paths
     * @return array
     */
    private function filterCaseSensitiveAssets(array $assets, array $paths): array
    {
        $filteredAssets = [];
        foreach ($assets as $asset) {
            foreach ($paths as $path) {
                if ($asset[self::MEDIA_GALLERY_ASSET_PATH] === $path) {
                    $filteredAssets[] = $asset;
                }
            }
        }

        return $filteredAssets;
    }
}
