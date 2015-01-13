<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product;

class Price implements \Magento\Indexer\Model\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Action\Row
     */
    protected $_productPriceIndexerRow;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Action\Rows
     */
    protected $_productPriceIndexerRows;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Action\Full
     */
    protected $_productPriceIndexerFull;

    /**
     * @param Price\Action\Row $productPriceIndexerRow
     * @param Price\Action\Rows $productPriceIndexerRows
     * @param Price\Action\Full $productPriceIndexerFull
     */
    public function __construct(
        \Magento\Catalog\Model\Indexer\Product\Price\Action\Row $productPriceIndexerRow,
        \Magento\Catalog\Model\Indexer\Product\Price\Action\Rows $productPriceIndexerRows,
        \Magento\Catalog\Model\Indexer\Product\Price\Action\Full $productPriceIndexerFull
    ) {
        $this->_productPriceIndexerRow = $productPriceIndexerRow;
        $this->_productPriceIndexerRows = $productPriceIndexerRows;
        $this->_productPriceIndexerFull = $productPriceIndexerFull;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        $this->_productPriceIndexerRows->execute($ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->_productPriceIndexerFull->execute();
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->_productPriceIndexerRows->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->_productPriceIndexerRow->execute($id);
    }
}
