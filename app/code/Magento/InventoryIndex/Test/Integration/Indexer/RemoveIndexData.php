<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndex\Test\Integration\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\MultiDimensionalIndex\Alias;
use Magento\Framework\MultiDimensionalIndex\IndexNameBuilder;
use Magento\InventoryIndex\Indexer\IndexStructure;

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
                ->setIndexId('inventory_stock')
                ->addDimension('stock_', (string)$stockId)
                ->setAlias(Alias::ALIAS_MAIN)
                ->build();
            $this->indexStructure->delete($indexName, ResourceConnection::DEFAULT_CONNECTION);
        }
    }
}
