<?php

namespace Magento\Inventory\Model\Indexer\StockItem\Action;

use Magento\Inventory\Model\Indexer\StockItem\AbstractAction;

/**
*/
class Full extends AbstractAction
{
    public function execute()
    {
        $this->reindexRows();
    }
}
