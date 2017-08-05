<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\Indexer;

use Magento\Inventory\Model\Indexer\StockItem\Action\Full as FullAction;
use Magento\Inventory\Model\Indexer\StockItem\Action\Row as RowAction;
use Magento\Inventory\Model\Indexer\StockItem\Action\Rows as RowsAction;


/**
 * Stock item Indexer @todo
 */
class StockItem implements \Magento\Framework\Indexer\ActionInterface
{

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
