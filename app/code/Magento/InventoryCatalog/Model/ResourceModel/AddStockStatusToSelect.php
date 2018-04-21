<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;

/**
 * Adapt adding stock status to select for Multi Stocks.
 */
class AddStockStatusToSelect
{
    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

    /**
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     */
    public function __construct(StockIndexTableNameResolverInterface $stockIndexTableNameResolver)
    {
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
    }

    /**
     * @param Select $select
     * @param int $stockId
     * @return void
     */
    public function execute(Select $select, int $stockId)
    {
        $tableName = $this->stockIndexTableNameResolver->execute($stockId);

        $select->joinLeft(
            ['stock_status' => $tableName],
            'e.sku = stock_status.sku',
            [IndexStructure::IS_SALABLE]
        );
    }
}
