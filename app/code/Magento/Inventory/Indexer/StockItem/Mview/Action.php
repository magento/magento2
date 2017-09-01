<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Indexer\StockItem\Mview;

use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\Mview\ActionInterface;
use Magento\Inventory\Indexer\StockItemIndexerInterface;

/**
 * Execute materialization on ids entities
 *
 * Extension pint for indexation
 *
 * @api
 */
class Action implements ActionInterface
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
     */
    public function execute($ids)
    {
        $indexer = $this->indexerFactory->create()->load(StockItemIndexerInterface::INDEXER_ID);
        $indexer->reindexList($ids);
    }
}
