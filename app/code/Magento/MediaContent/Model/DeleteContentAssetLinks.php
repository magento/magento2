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
 * Used to unassign relation of the media asset to the media content where the media asset is used
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
     * @param ContentAssetLinkInterface[] $contentAssetsLinks
     * @throws CouldNotDeleteException
     */
    public function execute(array $contentAssetsLinks): void
    {
        $failedLinks = [];
        foreach ($contentAssetsLinks as $contentAssetLink) {
            try {
                $connection = $this->resourceConnection->getConnection();
                $tableName = $this->resourceConnection->getTableName(self::MEDIA_CONTENT_ASSET_TABLE_NAME);
                $connection->delete(
                    $tableName,
                    [
                        self::ASSET_ID . ' = ?' => $contentAssetLink->getAssetId(),
                        self::ENTITY_TYPE . ' = ?' => $contentAssetLink->getContentId()->getEntityType(),
                        self::ENTITY_ID . ' = ?' => $contentAssetLink->getContentId()->getEntityId(),
                        self::FIELD . ' = ?' => $contentAssetLink->getField()
                    ]
                );
            } catch (\Exception $exception) {
                $this->logger->critical($exception);
                $failedLinks[] =  self::ASSET_ID . '=' . $contentAssetLink->getAssetId() .
                    self::ENTITY_TYPE . ' = ' . $contentAssetLink->getContentId()->getEntityType() .
                    self::ENTITY_ID . ' = ' . $contentAssetLink->getContentId()->getEntityId() .
                    self::FIELD . ' = ' . $contentAssetLink->getField();
            }
        }

        if (!empty($failedLinks)) {
            throw new CouldNotDeleteException(
                __(
                    'An error occurred at deleting link between the media asset and media content. Links: %links',
                    implode(' ,', $failedLinks)
                )
            );
        }
    }
}
