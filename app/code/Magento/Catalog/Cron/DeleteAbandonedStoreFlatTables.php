<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Cron;

class DeleteAbandonedStoreFlatTables
{
    /**
     * @var \Magento\Catalog\Helper\Product\Flat\Indexer
     */
    private $indexer;

    /**
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $indexer
     */
    public function __construct(
        \Magento\Catalog\Helper\Product\Flat\Indexer $indexer
    ) {
        $this->indexer = $indexer;
    }

    /**
     * Delete all product flat tables for not existing stores
     *
     * @return void
     */
    public function execute()
    {
        $this->indexer->deleteAbandonedStoreFlatTables();
    }
}
