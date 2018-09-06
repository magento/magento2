<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryIndexer\Indexer\Stock;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\InventoryCatalog\Model\ResourceModel\GetProductIdsByStockIds;
use Magento\InventoryIndexer\Indexer\Stock\StockIndexer;

/**
 * Reindex price after stock has reindexed.
 */
class PriceIndexUpdater
{
    /**
     * @var Processor
     */
    private $priceIndexProcessor;

    /**
     * @var GetProductIdsByStockIds
     */
    private $getProductIdsByStockIds;

    /**
     * @param Processor $priceIndexProcessor
     * @param GetProductIdsByStockIds $getProductIdsByStockIds
     */
    public function __construct(
        Processor $priceIndexProcessor,
        GetProductIdsByStockIds $getProductIdsByStockIds
    ) {
        $this->priceIndexProcessor = $priceIndexProcessor;
        $this->getProductIdsByStockIds = $getProductIdsByStockIds;
    }

    /**
     * @param StockIndexer $subject
     * @param $result
     * @param array $stockIds
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecuteList(
        StockIndexer $subject,
        $result,
        array $stockIds
    ): void {
        foreach ($this->getProductIdsByStockIds->execute($stockIds) as $productId) {
            $this->priceIndexProcessor->reindexRow($productId);
        }
    }
}
