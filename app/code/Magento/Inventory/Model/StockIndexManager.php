<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\Framework\MultiDimensionalIndex\IndexName;
use Magento\Framework\MultiDimensionalIndex\IndexNameBuilder;
use Magento\Inventory\Indexer\Stock\StockIndexer;

/**
 * Manager for stock index.
 */
class StockIndexManager
{
    /**
     * @var string
     */
    private $dimensionName = 'stock_';

    /**
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @param IndexNameBuilder $indexNameBuilder
     */
    public function __construct(
        IndexNameBuilder $indexNameBuilder
    ) {
        $this->indexNameBuilder = $indexNameBuilder;
    }

    /**
     * Build index by stock id and alias.
     *
     * @param string $stockId
     * @param string $alias
     *
     * @return IndexName
     */
    public function buildIndex(string $stockId, string $alias): IndexName
    {
        return $this->indexNameBuilder
            ->setIndexId(StockIndexer::INDEXER_ID)
            ->addDimension($this->dimensionName, $stockId)
            ->setAlias($alias)
            ->build();
    }

    /**
     * Get stock index table name by stock id.
     *
     * @param string $stockId
     *
     * @return string
     */
    public function getTableNameByStockId(string $stockId): string
    {
        return StockIndexer::INDEXER_ID . '_' . $this->dimensionName . $stockId;
    }
}
