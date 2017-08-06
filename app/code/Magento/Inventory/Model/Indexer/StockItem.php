<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\Indexer;

use Magento\Inventory\Model\Indexer\StockItem\IndexHandlerFactory;
use Magento\Inventory\Model\Indexer\StockItem\IndexStructure;

/**
 * Stock item Indexer @todo
 */
class StockItem implements \Magento\Framework\Indexer\ActionInterface
{

    /**
     * Indexer ID in configuration
     */
    const INDEXER_ID = 'inventory_stock_item_index';

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        // TODO: Implement executeFull() method.
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        // TODO: Implement executeList() method.
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        // TODO: Implement executeRow() method.
    }
}
