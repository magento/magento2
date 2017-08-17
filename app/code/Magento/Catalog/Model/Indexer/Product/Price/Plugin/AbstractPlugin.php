<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Price\Plugin;

/**
 * Class \Magento\Catalog\Model\Indexer\Product\Price\Plugin\AbstractPlugin
 *
 */
abstract class AbstractPlugin
{
    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     */
    public function __construct(\Magento\Framework\Indexer\IndexerRegistry $indexerRegistry)
    {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Invalidate indexer
     *
     * @return void
     */
    protected function invalidateIndexer()
    {
        $this->indexerRegistry->get(\Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID)->invalidate();
    }
}
