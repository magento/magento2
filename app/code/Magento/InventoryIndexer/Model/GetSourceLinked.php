<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\Inventory\Model\ResourceModel\StockSourceLink;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Is Source linked with Stock or Source Items.
 */
class GetSourceLinked
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Is Source linked with Stock or Source Items.
     *
     * @param string $sourceCode
     * @return bool
     */
    public function execute(string $sourceCode): bool
    {
        return $this->isSourceInTable(SourceItem::TABLE_NAME_SOURCE_ITEM, $sourceCode)
            && $this->isSourceInTable(StockSourceLink::TABLE_NAME_STOCK_SOURCE_LINK, $sourceCode);
    }

    /**
     * Returns true if in Table exist some records related to Source.
     *
     * @param string $tableName
     * @param string $sourceCode
     * @return bool
     */
    private function isSourceInTable(string $tableName, string $sourceCode): bool
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(
                $tableName,
                'COUNT(*)'
            )
            ->where(SourceInterface::SOURCE_CODE . ' = ?', $sourceCode);

        return (bool)$connection->fetchOne($select);
    }
}
