<?php
/**
 * @category    Magento
 * @package     Magento_CatalogInventory
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\Indexer;

class Stock implements \Magento\Indexer\Model\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Action\Row
     */
    protected $_productStockIndexerRow;

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Action\Rows
     */
    protected $_productStockIndexerRows;

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Action\Full
     */
    protected $_productStockIndexerFull;

    /**
     * @param Stock\Action\Row $productStockIndexerRow
     * @param Stock\Action\Rows $productStockIndexerRows
     * @param Stock\Action\Full $productStockIndexerFull
     */
    public function __construct(
        \Magento\CatalogInventory\Model\Indexer\Stock\Action\Row $productStockIndexerRow,
        \Magento\CatalogInventory\Model\Indexer\Stock\Action\Rows $productStockIndexerRows,
        \Magento\CatalogInventory\Model\Indexer\Stock\Action\Full $productStockIndexerFull
    ) {
        $this->_productStockIndexerRow = $productStockIndexerRow;
        $this->_productStockIndexerRows = $productStockIndexerRows;
        $this->_productStockIndexerFull = $productStockIndexerFull;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     *
     * @return void
     */
    public function execute($ids)
    {
        $this->_productStockIndexerRows->execute($ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->_productStockIndexerFull->execute();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     *
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->_productStockIndexerRows->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     *
     * @return void
     */
    public function executeRow($id)
    {
        $this->_productStockIndexerRow->execute($id);
    }
}
