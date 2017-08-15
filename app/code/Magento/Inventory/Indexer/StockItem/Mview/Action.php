<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer\StockItem\Mview;

use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Inventory\Indexer\StockItem;

/**
 * @todo add description
 */
class Action implements \Magento\Framework\Mview\ActionInterface
{

    /**
     * @var IndexerInterfaceFactory
     */
    private $indexerFactory;

    /**
     * @param IndexerInterfaceFactory $indexerFactory
     */
    public function __construct(IndexerInterfaceFactory $indexerFactory)
    {
        $this->indexerFactory = $indexerFactory;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     * @api
     */
    public function execute($ids)
    {
        /** @var \Magento\Framework\Indexer\IndexerInterface $indexer */
        $indexer = $this->indexerFactory->create()->load(StockItem::INDEXER_ID);
        $indexer->reindexList($ids);
    }
}
