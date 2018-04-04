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
class GetInvalidationRequired
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Is Inventory Indexer invalidation required after Source enabling or disabling.
     *
     * Returns 'true' only if Source 'enabled' value is changed, Source is linked to Stock and contains at least one
     * Stock Item.
     *
     * @param string $sourceCode
     * @param int $enabled
     * @return bool
     */
    public function execute(string $sourceCode, int $enabled): bool
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(
                ['tis' => $this->resource->getTableName(Source::TABLE_NAME_SOURCE)],
                'IF(tis.' . SourceInterface::ENABLED . '=' . $enabled . ', 0, 1)'
            )
            ->joinInner(
                ['isi' => $this->resource->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM)],
                'tis.' . SourceInterface::SOURCE_CODE . '=' . 'isi.' . SourceInterface::SOURCE_CODE,
                ''
            )->joinInner(
                ['issl' => $this->resource->getTableName(StockSourceLink::TABLE_NAME_STOCK_SOURCE_LINK)],
                'tis.' . SourceInterface::SOURCE_CODE . '=' . 'issl.' . SourceInterface::SOURCE_CODE,
                ''
            )
            ->where('tis.' . SourceInterface::SOURCE_CODE . ' = ?', $sourceCode);

        return (bool)$connection->fetchOne($select);
    }
}
