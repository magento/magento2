<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Indexer\Category\Product;

use Magento\Framework\Indexer\IndexTableRowSizeEstimatorInterface;

/**
 * Class RowSizeEstimator
 * Intended to estimate amount of memory necessary for saving the biggest category in the DB
 * @package Magento\Catalog\Model\Indexer\Category\Product
 */
class RowSizeEstimator implements IndexTableRowSizeEstimatorInterface
{
    /**
     * Amount of memory for index data row.
     */
    const ROW_MEMORY_SIZE = 100;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Calculate memory size for largest possible category in database.
     *
     * Result value is a multiplication of
     *  a) maximum amount of products in one category
     *  b) amount of store groups
     *  c) memory amount per each index row in DB table
     *
     * {@inheritdoc}
     */
    public function estimateRowSize()
    {
        $connection = $this->resourceConnection->getConnection();

        // get store groups count except the default
        $storeGroupSelect = $connection->select()
            ->from(
                $this->resourceConnection->getTableName('store_group'),
                ['count' => new \Zend_Db_Expr('count(*)')]
            )->where('group_id > 0');
        $storeGroupCount = $connection->fetchOne($storeGroupSelect);

        // get max possible products per category
        // subselect with products count per category
        $productCounterSubSelect = $connection->select()
            ->from(
                $this->resourceConnection->getTableName('catalog_category_product'),
                ['counter' => new \Zend_Db_Expr('count(category_id)')]
            )->group('category_id');

        // select maximum value from subselect
        $productCountSelect = $connection->select()
            ->from(
                ['counters' => $productCounterSubSelect],
                [new \Zend_Db_Expr('max(counter)')]
            );
        $maxProducts = $connection->fetchOne($productCountSelect);

        return ceil($storeGroupCount * $maxProducts * self::ROW_MEMORY_SIZE);
    }
}
