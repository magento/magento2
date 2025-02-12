<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product\Collection;

use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\DB\Select;

class JoinMinimalPosition
{
    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @param TableMaintainer $tableMaintainer
     */
    public function __construct(
        TableMaintainer $tableMaintainer
    ) {
        $this->tableMaintainer = $tableMaintainer;
    }

    /**
     * Add minimal position to the collection select
     *
     * @param Collection $collection
     * @param array $categoryIds
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Select_Exception
     */
    public function execute(Collection $collection, array $categoryIds): void
    {
        $positions = [];
        $connection = $collection->getConnection();
        $select = $collection->getSelect();

        foreach ($categoryIds as $categoryId) {
            $table = 'cat_index_' . $categoryId;
            $conditions = [
                $table . '.product_id=e.entity_id',
                $connection->quoteInto(
                    $table . '.store_id=?',
                    $collection->getStoreId(),
                    'int'
                ),
                $connection->quoteInto(
                    $table . '.category_id=?',
                    $categoryId,
                    'int'
                )
            ];

            $joinCond = implode(' AND ', $conditions);
            $fromPart = $select->getPart(Select::FROM);
            if (isset($fromPart[$table])) {
                $fromPart[$table]['joinCondition'] = $joinCond;
                $select->setPart(Select::FROM, $fromPart);
            } else {
                $select->joinLeft(
                    [$table => $this->tableMaintainer->getMainTable($collection->getStoreId())],
                    $joinCond,
                    []
                );
            }
            $positions[] = $connection->getIfNullSql($table . '.position', '~0');
        }

        // Ensures that position attribute is registered in _joinFields
        // in order for sort by position to use cat_index_position field
        $collection->addExpressionAttributeToSelect('position', 'cat_index_position', 'entity_id');

        $columns = $select->getPart(Select::COLUMNS);
        $preparedColumns = [];
        $columnFound = false;
        $minPos = $connection->getLeastSql($positions);

        // Remove columns with alias cat_index_position
        // Find column entry that was added in addExpressionAttributeToSelect. Expected [, cat_index_position, position]
        // and replace it with [, LEAST(...), cat_index_position]
        foreach ($columns as $columnEntry) {
            if ($columnEntry[2] !== 'cat_index_position') {
                if ($columnEntry[2] === 'position' && $columnEntry[1] === 'cat_index_position') {
                    if (!$columnFound) {
                        $columnEntry[1] = $minPos;
                        $columnEntry[2] = 'cat_index_position';
                        $columnFound = true;
                    } else {
                        continue;
                    }
                }
                $preparedColumns[] = $columnEntry;
            }
        }

        $select->setPart(Select::COLUMNS, $preparedColumns);

        if (!$columnFound) {
            $select->columns(['cat_index_position' => $minPos]);
        }
    }
}
