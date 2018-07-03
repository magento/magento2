<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Test\Integration\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryMultiDimensionalIndexerApi\Model\Alias;
use Magento\InventoryMultiDimensionalIndexerApi\Model\IndexNameBuilder;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;

class RemoveIndexData
{
    /**
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @var IndexStructure
     */
    private $indexStructure;

    /**
     * @param IndexNameBuilder $indexNameBuilder
     * @param IndexStructure $indexStructure
     */
    public function __construct(
        IndexNameBuilder $indexNameBuilder,
        IndexStructure $indexStructure
    ) {
        $this->indexNameBuilder = $indexNameBuilder;
        $this->indexStructure = $indexStructure;
    }

    /**
     * @param array $stockIds
     * @return void
     */
    public function execute(array $stockIds)
    {
        foreach ($stockIds as $stockId) {
            $indexName = $this->indexNameBuilder
                ->setIndexId(InventoryIndexer::INDEXER_ID)
                ->addDimension('stock_', (string)$stockId)
                ->setAlias(Alias::ALIAS_MAIN)
                ->build();
            $this->indexStructure->delete($indexName, ResourceConnection::DEFAULT_CONNECTION);
        }
    }
}
