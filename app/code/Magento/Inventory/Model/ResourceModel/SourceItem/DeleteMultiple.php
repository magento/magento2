<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel\SourceItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Implementation of SourceItem delete multiple operation for specific db layer
 * Delete Multiple used here for performance efficient purposes over single delete operation
 */
class DeleteMultiple
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
     * Multiple delete source items
     *
     * @param SourceItemInterface[] $sourceItems
     * @return void
     */
    public function execute(array $sourceItems)
    {
        if (!count($sourceItems)) {
            return;
        }
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(SourceItemResourceModel::TABLE_NAME_SOURCE_ITEM);

        $whereSql = $this->buildWhereSqlPart($sourceItems);
        $connection->delete($tableName, $whereSql);
    }

    /**
     * @param array $sourceItems
     * @return string
     */
    private function buildWhereSqlPart(array $sourceItems): string
    {
        $connection = $this->resourceConnection->getConnection();

        $condition = [];
        foreach ($sourceItems as $sourceItem) {
            $skuCondition = $connection->quoteInto(
                SourceItemInterface::SKU . ' = ?',
                $sourceItem->getSku()
            );
            $sourceCodeCondition = $connection->quoteInto(
                SourceItemInterface::SOURCE_CODE . ' = ?',
                $sourceItem->getSourceCode()
            );
            $condition[] = '(' . $skuCondition . ' AND ' . $sourceCodeCondition . ')';
        }
        return implode(' OR ', $condition);
    }
}
