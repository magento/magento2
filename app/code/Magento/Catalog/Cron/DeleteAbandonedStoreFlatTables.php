<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Cron;

/**
 * Class \Magento\Catalog\Cron\DeleteAbandonedStoreFlatTables
 *
 * @since 2.0.0
 */
class DeleteAbandonedStoreFlatTables
{
    /**
     * @var \Magento\Catalog\Helper\Product\Flat\Indexer
     * @since 2.0.0
     */
    private $indexer;

    /**
     * @param \Magento\Catalog\Helper\Product\Flat\Indexer $indexer
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function execute()
    {
        $this->indexer->deleteAbandonedStoreFlatTables();
    }
}
