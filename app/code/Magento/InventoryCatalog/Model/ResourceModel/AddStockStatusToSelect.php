<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\InventoryCatalog\Model\InStockConditionResolver;
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
     * @var InStockConditionResolver
     */
    private $inStockConditionResolver;

    /**
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param InStockConditionResolver $inStockConditionResolver
     */
    public function __construct(
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        InStockConditionResolver $inStockConditionResolver
    ) {
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->inStockConditionResolver = $inStockConditionResolver;
    }

    /**
     * @param Select $select
     * @param int $stockId
     * @return void
     */
    public function addStockStatusToSelect(Select $select, int $stockId)
    {
        $tableName = $this->stockIndexTableNameResolver->execute($stockId);
        $isSalableExpression = $select->getConnection()
            ->getCheckSql($this->inStockConditionResolver->execute('stock_status'), 1, 0);

        $select->joinLeft(
            ['stock_status' => $tableName],
            'e.sku = stock_status.sku',
            ['is_salable' => $isSalableExpression]
        );
    }
}
