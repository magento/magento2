<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\IntegrationException;
use Magento\MediaContentApi\Api\GetAssetsUsedInContentInterface;
use Psr\Log\LoggerInterface;

/**
 * Used to return media asset id list which is used in the specified media content
 */
class GetAssetsUsedInContent implements GetAssetsUsedInContentInterface
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
    public function execute(string $contentType, string $contentEntityId = null, string $contentField = null): array
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()
                ->from(
                    $this->resourceConnection->getTableName(self::MEDIA_CONTENT_ASSET_TABLE_NAME),
                    self::ASSET_ID
                )->where(self::TYPE . ' = ?', $contentType);

            if (null !== $contentEntityId) {
                $select = $select->where(self::ENTITY_ID . '= ?', $contentEntityId);
            }

            if (null !== $contentField) {
                $select = $select->where(self::FIELD . '= ?', $contentField);
            }

            return $connection->fetchAssoc($select);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            $message = __('An error occurred at getting asset used in content information.');
            throw new IntegrationException($message);
        }
    }
}
