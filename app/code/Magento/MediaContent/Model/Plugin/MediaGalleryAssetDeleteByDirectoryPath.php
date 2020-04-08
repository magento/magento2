<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model\Plugin;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\MediaContent\Model\RemoveRelationsForAssetIds;
use Magento\MediaGalleryApi\Model\Asset\Command\DeleteByDirectoryPathInterface;
use Psr\Log\LoggerInterface;

/**
 * Remove media content record after media gallery asset removal.
 */
class MediaGalleryAssetDeleteByDirectoryPath
{
    private const TABLE_MEDIA_GALLERY_ASSET = 'media_gallery_asset';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RemoveRelationsForAssetIds
     */
    private $removeRelationsForAssetIds;

    /**
     * @param RemoveRelationsForAssetIds $removeRelationsForAssetIds
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        RemoveRelationsForAssetIds $removeRelationsForAssetIds,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->removeRelationsForAssetIds = $removeRelationsForAssetIds;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * @param DeleteByDirectoryPathInterface $subject
     * @param \Closure $proceed
     * @param string $directoryPath
     * @throws CouldNotDeleteException
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        DeleteByDirectoryPathInterface $subject,
        \Closure $proceed,
        string $directoryPath
    ) : void {
        $assetIds = $this->getAssetIdsByDirectoryPath($directoryPath);

        $proceed();

        $this->removeRelationsForAssetIds->execute($assetIds);
    }

    /**
     * Get ids of media assets by directory path
     *
     * @param string $path
     * @return int[]
     */
    private function getAssetIdsByDirectoryPath(string $path): array
    {
        /** @var AdapterInterface $connection */
        $connection = $this->resourceConnection->getConnection();
        $galleryAssetTableName = $this->resourceConnection->getTableName(self::TABLE_MEDIA_GALLERY_ASSET);

        $select = $connection->select();
        $select->from($galleryAssetTableName, ['id']);
        $select->where('path LIKE ?', $path . '%');
        return $connection->fetchCol($select);
    }
}
