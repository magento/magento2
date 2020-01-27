<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider\SelectBuilderForAttribute;

use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * Join stock table with stock condition to select.
 *
 * @deprecated
 * @see \Magento\ElasticSearch
 */
class ApplyStockConditionToSelect
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
     * @param Select $select
     * @return Select
     */
    public function execute(Select $select): Select
    {
        $select->joinInner(
            ['stock_index' => $this->resource->getTableName('cataloginventory_stock_status')],
            'main_table.source_id = stock_index.product_id',
            []
        )->where('stock_index.stock_status = ?', Stock::STOCK_IN_STOCK);

        return $select;
    }
}
