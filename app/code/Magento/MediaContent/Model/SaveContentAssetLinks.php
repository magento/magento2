<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\MediaContentApi\Api\SaveContentAssetLinksInterface;
use Magento\MediaContentApi\Api\Data\ContentAssetLinkInterface;
use Psr\Log\LoggerInterface;

/**
 * Used for saving relation between the media asset and media content where the media asset is used
 */
class SaveContentAssetLinks implements SaveContentAssetLinksInterface
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
     * Save a media asset to content link.
     *
     * @param ContentAssetLinkInterface[] $contentAssetLinks
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(array $contentAssetLinks): void
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName(self::MEDIA_CONTENT_ASSET_TABLE_NAME);
            $data = [];
            foreach ($contentAssetLinks as $contentAssetLink) {
                $data[] = [
                    self::ASSET_ID => $contentAssetLink->getAssetId(),
                    self::ENTITY_TYPE => $contentAssetLink->getContentId()->getEntityType(),
                    self::ENTITY_ID => $contentAssetLink->getContentId()->getEntityId(),
                    self::FIELD => $contentAssetLink->getContentId()->getField()
                ];
            }
            $connection->insertMultiple($tableName, $data);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new CouldNotSaveException(
                __('An error occurred while saving relation between media asset and media content.'),
                $exception
            );
        }
    }
}
