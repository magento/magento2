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
        $assets = [];
        try {
            foreach ($this->getAssetsData($ids) as $assetData) {
                $assets[] = $this->assetFactory->create(
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
            throw new LocalizedException(__('Could not retrieve media assets'), $exception);
        }
        return $assets;
    }

    /**
     * Retrieve assets data from database
     *
     * @param array $ids
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    private function getAssetsData(array $ids): array
    {
        $mediaAssetTable = $this->resourceConnection->getTableName(self::TABLE_MEDIA_GALLERY_ASSET);
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(['amg' => $mediaAssetTable])
            ->where('amg.id IN (?)', $ids);
        return $connection->query($select)->fetchAll();
    }
}
