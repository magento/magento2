<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Asset\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Model\Asset\Command\SaveInterface;
use Psr\Log\LoggerInterface;

/**
 * Save media asset
 *
 * @deprecated 100.4.0 use \Magento\MediaGalleryApi\Api\SaveAssetsInterface instead
 * @see \Magento\MediaGalleryApi\Api\SaveAssetsInterface
 */
class Save implements SaveInterface
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
     * Save constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * Save media asset
     *
     * @param AssetInterface $mediaAsset
     *
     * @return int
     * @throws CouldNotSaveException
     */
    public function execute(AssetInterface $mediaAsset): int
    {
        try {
            /** @var \Magento\Framework\DB\Adapter\Pdo\Mysql $connection */
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName(self::TABLE_MEDIA_GALLERY_ASSET);
            $record = [
                'id' => $mediaAsset->getId(),
                'path' => $mediaAsset->getPath(),
                'title' => $mediaAsset->getTitle(),
                'source' => $mediaAsset->getSource(),
                'content_type' => $mediaAsset->getContentType(),
                'width' => $mediaAsset->getWidth(),
                'height' => $mediaAsset->getHeight(),
                'size' => $mediaAsset->getSize(),
            ];

            if ($mediaAsset->getCreatedAt()) {
                $record['created_at'] = $mediaAsset->getCreatedAt();
            }

            if ($mediaAsset->getUpdatedAt()) {
                $record['updated_at'] = $mediaAsset->getUpdatedAt();
            }

            $connection->insertOnDuplicate($tableName, $record);
            return (int)$connection->lastInsertId($tableName);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            $message = __('An error occurred during media asset save: %1', $exception->getMessage());
            throw new CouldNotSaveException($message, $exception);
        }
    }
}
