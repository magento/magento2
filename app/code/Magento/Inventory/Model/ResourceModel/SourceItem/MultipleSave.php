<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\ResourceModel\SourceItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Setup\InstallSchema;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Class MultipleSave
 */
class MultipleSave
{
    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * SourceCarrierLinkManagement constructor
     *
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
    public function multipleSave(array $sourceItems)
    {
        if (!empty($sourceItems)) {
            $connection = $this->connection->getConnection();
            $tableName = $connection->getTableName(InstallSchema::TABLE_NAME_SOURCE_ITEM);

            $columnsSql = $this->buildColumnsSqlPart([
                SourceItemInterface::SOURCE_ID,
                SourceItemInterface::SKU,
                SourceItemInterface::QUANTITY,
                SourceItemInterface::STATUS
            ]);
            $bind = [];
            $valuesSql = $this->buildValuesSqlPart($sourceItems, $bind);
            $onDuplicateSql = $this->buildOnDuplicateSqlPart([
                SourceItemInterface::SKU,
                SourceItemInterface::QUANTITY,
                SourceItemInterface::STATUS,
            ]);

            $insertSql = sprintf(
                'INSERT INTO %s (%s) VALUES %s %s',
                $tableName,
                $columnsSql,
                $valuesSql,
                $onDuplicateSql
            );
            $connection->query($insertSql, $bind);
        }
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
     * @param array $bind
     * @return string
     */
    private function buildValuesSqlPart(array $sourceItems, array &$bind)
    {
        $processedValues = [];
        foreach ($sourceItems as $sourceItem) {
            $bind = array_merge($bind, [
                $sourceItem->getSourceId(),
                $sourceItem->getSku(),
                $sourceItem->getQuantity(),
                $sourceItem->getStatus(),
            ]);
            $processedValues[] = '(?, ?, ?, ?)';
        }
        $sql = implode(', ', $processedValues);
        return $sql;
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
