<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\MediaContentApi\Api\UnassignAssetInterface;
use Psr\Log\LoggerInterface;

/**
 * Used to unassign relation of the media asset to the media content where the media asset is used
 */
class UnassignAsset implements UnassignAssetInterface
{
    private const MEDIA_CONTENT_ASSET_TABLE_NAME = 'media_content_asset';
    private const ASSET_ID = 'asset_id';
    private const TYPE = 'type';
    private const ENTITY_ID = 'entity_id';
    private const FIELD = 'field';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * GetAssetsUsedInContent constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(ResourceConnection $resourceConnection, LoggerInterface $logger)
    {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function execute(int $assetId, string $contentType, string $contentEntityId, string $contentField): void
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName(self::MEDIA_CONTENT_ASSET_TABLE_NAME);
            $connection->delete(
                $tableName,
                [
                    self::ASSET_ID . ' = ?' => $assetId,
                    self::TYPE . ' = ?' => $contentType,
                    self::ENTITY_ID . ' = ?' => $contentEntityId,
                    self::FIELD . ' = ?' => $contentField
                ]
            );
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            $message = __('An error occurred at unassign relation between the media asset and media content.');
            throw new CouldNotDeleteException($message);
        }
    }
}
