<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\IntegrationException;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use Magento\MediaContentApi\Api\GetContentByAssetIdsInterface;
use Psr\Log\LoggerInterface;

/**
 * Used to return media asset list for the specified asset.
 */
class GetContentByAssetIds implements GetContentByAssetIdsInterface
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
     * @var ContentIdentityInterfaceFactory
     */
    private $factory;

    /**
     * @param ContentIdentityInterfaceFactory $factory
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(
        ContentIdentityInterfaceFactory $factory,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->factory = $factory;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $assetIds): array
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()
                ->distinct()
                ->from(
                    $this->resourceConnection->getTableName(self::MEDIA_CONTENT_ASSET_TABLE_NAME),
                    ['entityType' => self::ENTITY_TYPE, 'entityId' => self::ENTITY_ID, self::FIELD]
                )
                ->where(self::ASSET_ID . ' IN (?)', $assetIds);

            $contentIdentities = [];
            foreach ($connection->fetchAll($select) as $contentIdentityData) {
                $contentIdentities[] = $this->factory->create($contentIdentityData);
            }
            return $contentIdentities;
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new IntegrationException(
                __('An error occurred at getting media asset to content relation by media asset id.')
            );
        }
    }
}
