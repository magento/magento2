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
 * Class GetByPath
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
        try {
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()
                ->from($this->resourceConnection->getTableName(self::TABLE_MEDIA_GALLERY_ASSET))
                ->where(self::MEDIA_GALLERY_ASSET_PATH . ' IN (?)', $paths);
            $assetsData = $connection->query($select)->fetchAll();
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

        $assets = [];

        foreach ($assetsData as $assetData) {
            $assets[] = $this->mediaAssetFactory->create(['data' => $assetData]);
        }

        return $assets;
    }
}
