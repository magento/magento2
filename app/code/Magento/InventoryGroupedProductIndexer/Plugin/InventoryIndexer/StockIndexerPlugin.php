<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryGroupedProductIndexer\Plugin\InventoryIndexer;

use Magento\Framework\Exception\StateException;
use Magento\InventoryGroupedProductIndexer\Indexer\Stock\StockIndexer as GroupedProductStockIndexer;
use Magento\InventoryIndexer\Indexer\Stock\StockIndexer;

class StockIndexerPlugin
{
    /**
     * @var GroupedProductStockIndexer
     */
    private $groupedProductStockIndexer;

    /**
     * @param GroupedProductStockIndexer $groupedProductStockIndexer
     */
    public function __construct(
        GroupedProductStockIndexer $groupedProductStockIndexer
    ) {
        $this->groupedProductStockIndexer = $groupedProductStockIndexer;
    }

    /**
     * @param StockIndexer $subject
     * @param void $result
     * @param array $stockIds
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws StateException
     */
    public function afterExecuteList(
        StockIndexer $subject,
        $result,
        array $stockIds
    ) {
        $this->groupedProductStockIndexer->executeList($stockIds);
    }
}
