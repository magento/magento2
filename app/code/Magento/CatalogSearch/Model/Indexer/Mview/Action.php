<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Mview;

use Magento\CatalogSearch\Model\Indexer\Fulltext;
use Magento\Framework\Mview\ActionInterface;
use Magento\Framework\Indexer\IndexerInterfaceFactory;

/**
 * Class \Magento\CatalogSearch\Model\Indexer\Mview\Action
 *
 * @since 2.0.0
 */
class Action implements ActionInterface
{
    /**
     * @var IndexerInterfaceFactory
     * @since 2.0.0
     */
    private $indexerFactory;

    /**
     * @param IndexerInterfaceFactory $indexerFactory
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function execute($ids)
    {
        /** @var \Magento\Framework\Indexer\IndexerInterface $indexer */
        $indexer = $this->indexerFactory->create()->load(Fulltext::INDEXER_ID);
        $indexer->reindexList($ids);
    }
}
