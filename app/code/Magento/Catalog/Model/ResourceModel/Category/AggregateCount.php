<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\ResourceModel\Category;

use Magento\Catalog\Model\Category;

/**
 * Aggregate count for parent category after deleting child category
 *
 * Class AggregateCount
 */
class AggregateCount
{
    /**
     * Reduces children count for parent categories
     *
     * @param Category $category
     * @return void
     */
    public function processDelete(Category $category)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category $resourceModel */
        $resourceModel = $category->getResource();
        $connection = $resourceModel->getConnection();
        $entityTable = $resourceModel->getEntityTable();
        /**
         * Update children count for all parent categories
         */
        $parentIds = $category->getParentIds();
        $childrenCount = $this->getCategoryRowCount(
            $connection,
            $entityTable,
            $category->getId()
        );
        if ($parentIds) {
            $data = ['children_count' => new \Zend_Db_Expr('children_count - '.$childrenCount)];
            $where = ['entity_id IN(?)' => $parentIds];
            $connection->update($entityTable, $data, $where);
        }
    }

    /**
     * To get count of rows (category count) for a specific entity ID
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param string $table
     * @param int $categoryId
     * @return int
     */
    private function getCategoryRowCount(
        \Magento\Framework\DB\Adapter\AdapterInterface $connection,
        string $table,
        $categoryId
    ): int {
        // staging preview modifiers (created_in/updated_in) are not applied.
        $sql = sprintf(
            'SELECT COUNT(*) FROM %s WHERE entity_id = ?',
            $connection->quoteIdentifier($table)
        );

        return (int)$connection->fetchOne($sql, [(int)$categoryId]);
    }
}
