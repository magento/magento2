<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Indexer\Source\Mview;

use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\Mview\ActionInterface;
use Magento\Inventory\Indexer\Source\SourceIndexerInterface;

/**
 * Execute materialization on entity ids
 *
 * Extension point for indexation
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
        $indexer = $this->indexerFactory->create()->load(SourceIndexerInterface::INDEXER_ID);
        $indexer->reindexList($ids);
    }
}
