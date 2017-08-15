<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Indexer;

/**
 * Stock item Indexer @todo
 */
interface StockItemIndexerInterface extends \Magento\Framework\Indexer\ActionInterface
{

    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'inventory_stock_item';

    /**
     * Returns the indexer name.
     *
     * @return string
     */
    public function getName();
}
