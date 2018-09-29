<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\Source;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\Inventory\Model\ResourceModel\StockSourceLink;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Is Inventory Indexer invalidation required after Source enabling or disabling.
 */
class IsInvalidationRequiredForSource
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Returns 'true' only if Source 'enabled' value is changed, Source is linked to Stock and contains at least one
     * Source Item.
     *
     * @param string $sourceCode
     * @param bool $enabled
     * @return bool
     */
    public function execute(string $sourceCode, bool $enabled): bool
    {
        $connection = $this->resourceConnection->getConnection();
        $sourceTable = $this->resourceConnection->getTableName(Source::TABLE_NAME_SOURCE);
        $sourceItemTable = $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM);
        $stockSourceLinkTable = $this->resourceConnection->getTableName(StockSourceLink::TABLE_NAME_STOCK_SOURCE_LINK);

        $select = $connection->select()
            ->from(
                ['sources' => $sourceTable],
                '(sources.' . SourceInterface::ENABLED . ' != ' . (int)$enabled . ')'
            )
            ->joinInner(
                ['source_item' => $sourceItemTable],
                'sources.' . SourceInterface::SOURCE_CODE . '=' . 'source_item.' . SourceInterface::SOURCE_CODE,
                null
            )->joinInner(
                ['stock_source_link' => $stockSourceLinkTable],
                'sources.' . SourceInterface::SOURCE_CODE . '=' . 'stock_source_link.' . SourceInterface::SOURCE_CODE,
                null
            )
            ->where('sources.' . SourceInterface::SOURCE_CODE . ' = ?', $sourceCode);

        return (bool)$connection->fetchOne($select);
    }
}
