<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Inventory\Setup\InstallSchema;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Class SourceItem
 */
class SourceItem extends AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(InstallSchema::TABLE_NAME_SOURCE_ITEM, SourceItemInterface::SOURCE_ITEM_ID);
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
            $connection = $this->getConnection();
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
        $processedColumns = array_map([$this->getConnection(), 'quoteIdentifier'], $columns);
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
        $processedFields = [];
        foreach ($fields as $field) {
            $processedFields[] = sprintf('%1$s = VALUES(%1$s)', $this->getConnection()->quoteIdentifier($field));
        }
        $sql = 'ON DUPLICATE KEY UPDATE ' . implode(', ', $processedFields);
        return $sql;
    }
}
