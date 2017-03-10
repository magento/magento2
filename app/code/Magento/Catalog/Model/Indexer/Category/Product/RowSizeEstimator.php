<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Indexer\Category\Product;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\IndexTableRowSizeEstimator;
use Magento\Framework\Indexer\IndexTableRowSizeEstimatorInterface;

class RowSizeEstimator implements IndexTableRowSizeEstimatorInterface
{
    /**
     * @var IndexTableRowSizeEstimator
     */
    private $indexTableRowSizeEstimator;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param IndexTableRowSizeEstimator $indexTableRowSizeEstimator
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        IndexTableRowSizeEstimator $indexTableRowSizeEstimator
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->indexTableRowSizeEstimator = $indexTableRowSizeEstimator;
    }

    /**
     * Calculate memory size for largest possible category in database.
     *
     * @inheritdoc
     */
    public function estimateRowSize()
    {
        $connection = $this->resourceConnection->getConnection();

        // get store groups count except the default
        $storeGroupSelect = $connection->select()
            ->from($this->resourceConnection->getTableName('store_group'))
            ->reset('columns')
            ->columns(['count' => new \Zend_Db_Expr('count(*)')])
            ->where('group_id > 0');
        $storeGroupCount = $connection->fetchOne($storeGroupSelect);

        // get max possible products per category
        $productCountSelect = $connection->select()
            ->from($this->resourceConnection->getTableName('catalog_category_product'))
            ->reset('columns')
            ->columns(['counter' => new \Zend_Db_Expr('count(category_id)')])
            ->group('category_id')
            ->order('counter ' . \Magento\Framework\DB\Select::SQL_DESC)
            ->limit(1);
        $maxProducts = $connection->fetchOne($productCountSelect);

        return ceil($storeGroupCount * $maxProducts);
    }
}
