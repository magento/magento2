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
class StockItem implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{

    /**
     * @var FullAction
     */
    private $fullAction;

    /**
     * @var RowAction
     */
    private $rowAction;

    /**
     * @var RowsAction
     */
    private $rowsAction;

    /**
     * StockItem constructor.
     * @param FullAction $fullAction
     * @param RowAction $rowAction
     * @param  RowsAction $rowsAction
     */
    public function __construct(FullAction $fullAction, RowAction $rowAction, RowsAction $rowsAction)
    {
        $this->fullAction = $fullAction;
        $this->rowAction = $rowAction;
        $this->rowsAction = $rowsAction;
    }


    /**
     * @inheritdoc
     */
    public function execute($ids)
    {
        $this->rowsAction->execute($ids);
    }

    /**
     * @inheritdoc
     */
    public function executeFull()
    {
        $this->fullAction->execute();
    }

    /**
     * @inheritdoc
     */
    public function executeList(array $ids)
    {
        $this->rowsAction->execute($ids);
    }

    /**
     * @inheritdoc
     */
    public function executeRow($id)
    {
        $this->rowAction->execute($id);
    }
}
