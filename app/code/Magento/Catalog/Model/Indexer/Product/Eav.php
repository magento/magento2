<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product;

class Eav implements \Magento\Indexer\Model\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Action\Row
     */
    protected $_productEavIndexerRow;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Action\Rows
     */
    protected $_productEavIndexerRows;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Action\Full
     */
    protected $_productEavIndexerFull;

    /**
     * @param Eav\Action\Row $productEavIndexerRow
     * @param Eav\Action\Rows $productEavIndexerRows
     * @param Eav\Action\Full $productEavIndexerFull
     */
    public function __construct(
        \Magento\Catalog\Model\Indexer\Product\Eav\Action\Row $productEavIndexerRow,
        \Magento\Catalog\Model\Indexer\Product\Eav\Action\Rows $productEavIndexerRows,
        \Magento\Catalog\Model\Indexer\Product\Eav\Action\Full $productEavIndexerFull
    ) {
        $this->_productEavIndexerRow = $productEavIndexerRow;
        $this->_productEavIndexerRows = $productEavIndexerRows;
        $this->_productEavIndexerFull = $productEavIndexerFull;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        $this->_productEavIndexerRows->execute($ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->_productEavIndexerFull->execute();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->_productEavIndexerRows->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->_productEavIndexerRow->execute($id);
    }
}
