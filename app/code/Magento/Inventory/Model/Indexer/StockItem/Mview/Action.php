<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\Indexer\StockItem\Mview;

use Magento\Inventory\Model\Indexer\StockItem\Action\Full as FullAction;
use Magento\Inventory\Model\Indexer\StockItem\Action\Row as RowAction;
use Magento\Inventory\Model\Indexer\StockItem\Action\Rows as RowsAction;


/**
 * @todo add description
 */
class Action implements \Magento\Framework\Mview\ActionInterface
{
    /**
     *  Rebuild the stock item index
     *
     * {@inheritdoc}
     */
    public function execute($ids)
    {
        // TODO: Implement execute() method.
    }
}
