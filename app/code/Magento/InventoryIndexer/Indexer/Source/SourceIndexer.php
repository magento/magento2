<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Indexer\Source;

use Magento\InventoryIndexer\Indexer\Stock\StockIndexer;

/**
 * Source indexer
 *
 * @api
 */
class SourceIndexer
{
    /**
     * @var GetAssignedStockIds
     */
    private $getAssignedStockIds;

    /**
     * @var StockIndexer
     */
    private $stockIndexer;

    /**
     * @param GetAssignedStockIds $getAssignedStockIds
     * @param StockIndexer $stockIndexer
     */
    public function __construct(
        GetAssignedStockIds $getAssignedStockIds,
        StockIndexer $stockIndexer
    ) {
        $this->getAssignedStockIds = $getAssignedStockIds;
        $this->stockIndexer = $stockIndexer;
    }

    /**
     * @return void
     */
    public function executeFull()
    {
        $this->stockIndexer->executeFull();
    }

    /**
     * @param string $sourceCode
     * @return void
     */
    public function executeRow(string $sourceCode)
    {
        $this->executeList([$sourceCode]);
    }

    /**
     * @param array $sourceCodes
     */
    public function executeList(array $sourceCodes)
    {
        $stockIds = $this->getAssignedStockIds->execute($sourceCodes);
        $this->stockIndexer->executeList($stockIds);
    }
}
