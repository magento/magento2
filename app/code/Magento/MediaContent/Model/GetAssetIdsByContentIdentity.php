<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\IntegrationException;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;
use Magento\MediaContentApi\Api\GetAssetIdsByContentIdentityInterface;
use Psr\Log\LoggerInterface;

/**
 * Used to return media asset id list which is used in the specified media content
 */
class GetAssetIdsByContentIdentity implements GetAssetIdsByContentIdentityInterface
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
     * @inheritdoc
     */
    public function execute(ContentIdentityInterface $contentIdentity): array
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()
                ->from(
                    $this->resourceConnection->getTableName(self::MEDIA_CONTENT_ASSET_TABLE_NAME),
                    self::ASSET_ID
                )->where(
                    self::ENTITY_TYPE . ' = ?',
                    $contentIdentity->getEntityType()
                )->where(
                    self::ENTITY_ID . '= ?',
                    $contentIdentity->getEntityId()
                )->where(
                    self::FIELD . '= ?',
                    $contentIdentity->getField()
                );

            return array_keys($connection->fetchAssoc($select));
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            $message = __('An error occurred at getting asset used in content information.');
            throw new IntegrationException($message);
        }
    }
}
