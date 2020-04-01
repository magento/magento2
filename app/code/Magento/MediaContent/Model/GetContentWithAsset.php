<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\IntegrationException;
use Magento\MediaContentApi\Api\GetContentWithAssetInterface;
use Psr\Log\LoggerInterface;

/**
 * Used to return media asset list for the specified asset.
 */
class GetContentWithAsset implements GetContentWithAssetInterface
{
    private const MEDIA_CONTENT_ASSET_TABLE_NAME = 'media_content_asset';
    private const ASSET_ID = 'asset_id';

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
    public function execute(int $assetId): array
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()
                ->from($this->resourceConnection->getTableName(self::MEDIA_CONTENT_ASSET_TABLE_NAME))
                ->where(self::ASSET_ID . '= ?', $assetId);

            return $connection->fetchAssoc($select);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            $message = __('An error occurred at getting media asset to content relation by media asset id.');
            throw new IntegrationException($message);
        }
    }
}
