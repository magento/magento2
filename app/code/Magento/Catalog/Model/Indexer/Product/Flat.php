<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product;

class Flat implements \Magento\Indexer\Model\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Row
     */
    protected $_productFlatIndexerRow;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows
     */
    protected $_productFlatIndexerRows;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Action\Full
     */
    protected $_productFlatIndexerFull;

    /**
     * @param Flat\Action\Row $productFlatIndexerRow
     * @param Flat\Action\Rows $productFlatIndexerRows
     * @param Flat\Action\Full $productFlatIndexerFull
     */
    public function __construct(
        \Magento\Catalog\Model\Indexer\Product\Flat\Action\Row $productFlatIndexerRow,
        \Magento\Catalog\Model\Indexer\Product\Flat\Action\Rows $productFlatIndexerRows,
        \Magento\Catalog\Model\Indexer\Product\Flat\Action\Full $productFlatIndexerFull
    ) {
        $this->_productFlatIndexerRow = $productFlatIndexerRow;
        $this->_productFlatIndexerRows = $productFlatIndexerRows;
        $this->_productFlatIndexerFull = $productFlatIndexerFull;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        $this->_productFlatIndexerRows->execute($ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->_productFlatIndexerFull->execute();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->_productFlatIndexerRows->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->_productFlatIndexerRow->execute($id);
    }
}
