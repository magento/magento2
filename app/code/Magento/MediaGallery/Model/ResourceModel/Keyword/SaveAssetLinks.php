<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\ResourceModel\Keyword;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\MediaGalleryApi\Api\Data\KeywordInterface;
use Psr\Log\LoggerInterface;

/**
 * Save links between asset and keyword to media_gallery_asset_keyword table
 */
class SaveAssetLinks
{
    private const TABLE_ASSET_KEYWORD = 'media_gallery_asset_keyword';
    private const FIELD_ASSET_ID = 'asset_id';
    private const FIELD_KEYWORD_ID = 'keyword_id';

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
    public function __construct(
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * Save asset keywords links
     *
     * @param int $assetId
     * @param KeywordInterface[] $keywordIds
     *
     * @throws CouldNotSaveException
     */
    public function execute(int $assetId, array $keywordIds): void
    {
        try {
            $values = [];
            foreach ($keywordIds as $keywordId) {
                $values[] = [$assetId, $keywordId];
            }

            if (!empty($values)) {
                /** @var Mysql $connection */
                $connection = $this->resourceConnection->getConnection();
                $connection->insertArray(
                    $this->resourceConnection->getTableName(self::TABLE_ASSET_KEYWORD),
                    [self::FIELD_ASSET_ID, self::FIELD_KEYWORD_ID],
                    $values,
                    AdapterInterface::INSERT_IGNORE
                );
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new CouldNotSaveException(
                __('Could not save asset keyword links'),
                $exception
            );
        }
    }
}
