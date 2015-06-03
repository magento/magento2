<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

trait IndexerTrait
{
    /**
     * @var IndexerInterface
     */
    protected $indexer;

    /**
     * Set indexer for access to configuration
     *
     * @param $indexer
     * @return void
     */
    public function setIndexer(IndexerInterface $indexer)
    {
        $this->indexer = $indexer;
    }
}
