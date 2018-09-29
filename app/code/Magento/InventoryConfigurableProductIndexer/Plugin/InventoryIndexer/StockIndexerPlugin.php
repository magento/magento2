<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProductIndexer\Plugin\InventoryIndexer;

use Magento\Framework\Exception\StateException;
use Magento\InventoryConfigurableProductIndexer\Indexer\Stock\StockIndexer as ConfigurableProductsStockIndexer;
use Magento\InventoryIndexer\Indexer\Stock\StockIndexer;

class StockIndexerPlugin
{
    /**
     * @var ConfigurableProductsStockIndexer
     */
    private $configurableProductsStockIndexer;

    /**
     * @param ConfigurableProductsStockIndexer $configurableProductsStockIndexer
     */
    public function __construct(
        ConfigurableProductsStockIndexer $configurableProductsStockIndexer
    ) {
        $this->configurableProductsStockIndexer = $configurableProductsStockIndexer;
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
        $this->configurableProductsStockIndexer->executeList($stockIds);
    }
}
