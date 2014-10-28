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
    public function executeList($ids)
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
