<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    public function executeList($ids)
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
