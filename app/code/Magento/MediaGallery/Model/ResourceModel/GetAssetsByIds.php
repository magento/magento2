<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Magento\MediaGalleryApi\Api\GetAssetsByIdsInterface;
use Psr\Log\LoggerInterface;

/**
 * Get media assets by ids
 */
class GetAssetsByIds implements GetAssetsByIdsInterface
{
    private const TABLE_MEDIA_GALLERY_ASSET = 'media_gallery_asset';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AssetInterfaceFactory
     */
    private $assetFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GetById constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param AssetInterfaceFactory $assetFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        AssetInterfaceFactory $assetFactory,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->assetFactory = $assetFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $ids): array
    {
        try {
            $mediaAssetTable = $this->resourceConnection->getTableName(self::TABLE_MEDIA_GALLERY_ASSET);
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()
                ->from(['amg' => $mediaAssetTable])
                ->where('amg.id IN (?)', $ids);
            $assetsData = $connection->query($select)->fetchAll();
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new LocalizedException(__('Could not retrieve media assets'), $exception);
        }

        $assets = [];

        foreach ($assetsData as $assetData) {
            $assets[] = $this->assetFactory->create(['data' => $assetData]);
        }

        return $assets;
    }
}
