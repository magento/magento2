<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\Product;

use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Imported product stock manager
 */
class StockProcessor
{
    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;
    /**
     * @var array
     */
    private $indexers;

    /**
     * Initializes dependencies.
     *
     * @param IndexerRegistry $indexerRegistry
     * @param array $indexers
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        array $indexers = []
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->indexers = array_filter($indexers);
    }

    /**
     * Reindex products by ids
     *
     * @param array $ids
     * @return void
     */
    public function reindexList(array $ids = []): void
    {
        if ($ids) {
            foreach ($this->indexers as $indexerName) {
                $indexer = $this->indexerRegistry->get($indexerName);
                if (!$indexer->isScheduled()) {
                    $indexer->reindexList($ids);
                }
            }
        }
    }
}
