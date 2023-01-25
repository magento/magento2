<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\MediaContentApi\Api\Data\ContentAssetLinkInterface;
use Magento\MediaContentApi\Api\DeleteContentAssetLinksInterface;
use Psr\Log\LoggerInterface;

/**
 * Used to delete links of the media asset to the media content
 */
class DeleteContentAssetLinks implements DeleteContentAssetLinksInterface
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
     * Remove relation between the media asset and the content. I.e media asset no longer part of the content
     *
     * @param ContentAssetLinkInterface[] $contentAssetLinks
     * @throws CouldNotDeleteException
     */
    public function execute(array $contentAssetLinks): void
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName(self::MEDIA_CONTENT_ASSET_TABLE_NAME);
            $whereSql = $this->buildWhereSqlPart($contentAssetLinks);
            $connection->delete($tableName, $whereSql);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new CouldNotDeleteException(
                __('An error occurred at deleting links between the media asset and media content.')
            );
        }
    }

    /**
     * Build sql where condition
     *
     * @param ContentAssetLinkInterface[] $contentAssetLinks
     * @return string
     */
    private function buildWhereSqlPart(array $contentAssetLinks): string
    {
        $connection = $this->resourceConnection->getConnection();
        $condition = [];
        foreach ($contentAssetLinks as $contentAssetLink) {
            $assetId = $connection->quoteInto(self::ASSET_ID . ' = ?', $contentAssetLink->getAssetId());
            $entityId = $connection->quoteInto(
                self::ENTITY_ID . ' = ?',
                $contentAssetLink->getContentId()->getEntityId()
            );
            $entityType = $connection->quoteInto(
                self::ENTITY_TYPE . ' = ?',
                $contentAssetLink->getContentId()->getEntityType()
            );
            $field = $connection->quoteInto(
                self::FIELD . ' = ?',
                $contentAssetLink->getContentId()->getField()
            );
            $condition[] = '(' . $assetId . ' AND ' . $entityId . ' AND ' . $entityType . ' AND ' . $field . ')';
        }
        return implode(' OR ', $condition);
    }
}
