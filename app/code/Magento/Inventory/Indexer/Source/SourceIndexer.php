<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Indexer\Source;

use Magento\Framework\Indexer\ActionInterface;
use Magento\Inventory\Indexer\Stock\StockIndexer;

/**
 * Source indexer
 * Extension point for indexation
 *
 * @api
 */
class SourceIndexer implements ActionInterface
{
    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'inventory_source';

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
     * @inheritdoc
     */
    public function executeFull()
    {
        $this->stockIndexer->executeFull();
    }

    /**
     * @inheritdoc
     */
    public function executeRow($sourceCode)
    {
        $this->executeList([$sourceCode]);
    }

    /**
     * @inheritdoc
     */
    public function executeList(array $sourceCode)
    {
        $stockIds = $this->getAssignedStockIds->execute($sourceCode);
        $this->stockIndexer->executeList($stockIds);
    }
}
