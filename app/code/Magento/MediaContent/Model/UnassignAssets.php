<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;
use Magento\MediaContentApi\Api\UnassignAssetsInterface;
use Psr\Log\LoggerInterface;

/**
 * Used to unassign relation of the media asset to the media content where the media asset is used
 */
class UnassignAssets implements UnassignAssetsInterface
{
    private const MEDIA_CONTENT_ASSET_TABLE_NAME = 'media_content_asset';
    private const ASSET_ID = 'asset_id';
    private const ENTITY_TYPE = 'entity_type';
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
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(ResourceConnection $resourceConnection, LoggerInterface $logger)
    {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(ContentIdentityInterface $contentIdentity, array $assetIds): void
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName(self::MEDIA_CONTENT_ASSET_TABLE_NAME);
            $connection->delete(
                $tableName,
                [
                    self::ASSET_ID . ' IN (?)' => $assetIds,
                    self::ENTITY_TYPE . ' = ?' => $contentIdentity->getEntityType(),
                    self::ENTITY_ID . ' = ?' => $contentIdentity->getEntityId(),
                    self::FIELD . ' = ?' => $contentIdentity->getField()
                ]
            );
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new CouldNotDeleteException(
                __('An error occurred at unassign relation between the media asset and media content.'),
                $exception
            );
        }
    }
}
