<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\ResourceModel\SourceItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Implementation of SourceItem save multiple operation for specific db layer
 * Save Multiple used here for performance efficient purposes over single save operation
 */
class SaveMultiple
{
    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @param ResourceConnection $connection
     */
    public function __construct(
        ResourceConnection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * Multiple save source items
     *
     * @param SourceItemInterface[] $sourceItems
     * @return void
     */
    public function execute(array $sourceItems)
    {
        $connection = $this->connection->getConnection();
        $tableName = $connection->getTableName(SourceItemResourceModel::TABLE_NAME_SOURCE_ITEM);

        $columnsSql = $this->buildColumnsSqlPart([
            SourceItemInterface::SOURCE_ID,
            SourceItemInterface::SKU,
            SourceItemInterface::QUANTITY,
            SourceItemInterface::STATUS
        ]);
        $valuesSql = $this->buildValuesSqlPart($sourceItems);
        $onDuplicateSql = $this->buildOnDuplicateSqlPart([
            SourceItemInterface::SKU,
            SourceItemInterface::QUANTITY,
            SourceItemInterface::STATUS,
        ]);
        $bind = $this->getSqlBindData($sourceItems);

        $insertSql = sprintf(
            'INSERT INTO %s (%s) VALUES %s %s',
            $tableName,
            $columnsSql,
            $valuesSql,
            $onDuplicateSql
        );
        $connection->query($insertSql, $bind);
    }

    /**
     * @param array $columns
     * @return string
     */
    private function buildColumnsSqlPart(array $columns)
    {
        $connection = $this->connection->getConnection();
        $processedColumns = array_map([$connection, 'quoteIdentifier'], $columns);
        $sql = implode(', ', $processedColumns);
        return $sql;
    }

    /**
     * @param SourceItemInterface[] $sourceItems
     * @return string
     */
    private function buildValuesSqlPart(array $sourceItems)
    {
        $sql = rtrim(str_repeat('(?, ?, ?, ?), ', count($sourceItems)), ', ');
        return $sql;
    }

    /**
     * @param SourceItemInterface[] $sourceItems
     * @return string
     */
    private function getSqlBindData(array $sourceItems)
    {
        $bind = [];
        foreach ($sourceItems as $sourceItem) {
            $bind = array_merge($bind, [
                $sourceItem->getSourceId(),
                $sourceItem->getSku(),
                $sourceItem->getQuantity(),
                $sourceItem->getStatus(),
            ]);
        }
        return $bind;
    }

    /**
     * @param array $fields
     * @return string
     */
    private function buildOnDuplicateSqlPart(array $fields)
    {
        $connection = $this->connection->getConnection();
        $processedFields = [];
        foreach ($fields as $field) {
            $processedFields[] = sprintf('%1$s = VALUES(%1$s)', $connection->quoteIdentifier($field));
        }
        $sql = 'ON DUPLICATE KEY UPDATE ' . implode(', ', $processedFields);
        return $sql;
    }
}
