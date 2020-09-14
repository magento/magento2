<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\MediaContentApi\Api\DeleteContentAssetLinksByAssetIdsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Psr\Log\LoggerInterface;

/**
 * Delete the relation between media asset and the piece of content. I.e media asset no longer part of the content
 */
class DeleteContentAssetLinksByAssetIds implements DeleteContentAssetLinksByAssetIdsInterface
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
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     */
    public function __construct(ResourceConnection $resourceConnection, LoggerInterface $logger)
    {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * Delete media content relations by media asset ids
     *
     * @param array $assetIds
     * @throws CouldNotDeleteException
     */
    public function execute(array $assetIds): void
    {
        $commaSeparatedAssetIds = implode(', ', $assetIds);
        try {
            $this->resourceConnection->getConnection()->delete(
                $this->resourceConnection->getTableName(self::MEDIA_CONTENT_ASSET_TABLE_NAME),
                [
                    self::ASSET_ID . ' IN (?)' => $commaSeparatedAssetIds
                 ]
            );
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            $message = __(
                'Could not remove media content relations for assets ids: %ids',
                [
                    'ids' => $commaSeparatedAssetIds
                ]
            );
            throw new CouldNotDeleteException($message, $exception);
        }
    }
}
