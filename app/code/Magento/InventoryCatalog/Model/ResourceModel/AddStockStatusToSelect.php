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
    private $stockIndexTableProvider;

    /**
     * @param StockIndexTableNameResolverInterface $stockIndexTableProvider
     */
    public function __construct(StockIndexTableNameResolverInterface $stockIndexTableProvider)
    {
        $this->stockIndexTableProvider = $stockIndexTableProvider;
    }

    /**
     * @param Select $select
     * @param int $stockId
     *
     * @return void
     */
    public function addStockStatusToSelect(Select $select, int $stockId)
    {
        $tableName = $this->stockIndexTableProvider->execute($stockId);
        $isSalableExpression = $select->getConnection()->getCheckSql(
            'stock_status.' . IndexStructure::QUANTITY . ' > 0',
            1,
            0
        );

        $select->joinLeft(
            ['stock_status' => $tableName],
            'e.sku = stock_status.sku',
            ['is_salable' => $isSalableExpression]
        );
    }
}
