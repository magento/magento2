<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Test\Integration\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\MultiDimensionalIndex\Alias;
use Magento\Inventory\Indexer\IndexStructure;
use Magento\Inventory\Model\StockIndexManager;

class RemoveIndexData
{
    /**
     * @var StockIndexManager
     */
    private $stockIndexManager;

    /**
     * @var IndexStructure
     */
    private $indexStructure;

    /**
     * @param StockIndexManager $stockIndexManager
     * @param IndexStructure $indexStructure
     */
    public function __construct(
        StockIndexManager $stockIndexManager,
        IndexStructure $indexStructure
    ) {
        $this->stockIndexManager = $stockIndexManager;
        $this->indexStructure = $indexStructure;
    }

    /**
     * @param array $stockIds
     * @return void
     */
    public function execute(array $stockIds)
    {
        foreach ($stockIds as $stockId) {
            $indexName = $this->stockIndexManager->buildIndex((string)$stockId, Alias::ALIAS_MAIN);

            $this->indexStructure->delete($indexName, ResourceConnection::DEFAULT_CONNECTION);
        }
    }
}
