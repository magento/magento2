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

class EavTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Action\Row|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productEavIndexerRow;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Action\Rows|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productEavIndexerRows;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Action\Full|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_productEavIndexerFull;

    protected function setUp()
    {
        $this->_productEavIndexerRow = $this->getMockBuilder('Magento\Catalog\Model\Indexer\Product\Eav\Action\Row')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_productEavIndexerRows = $this->getMockBuilder('Magento\Catalog\Model\Indexer\Product\Eav\Action\Rows')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_productEavIndexerFull = $this->getMockBuilder('Magento\Catalog\Model\Indexer\Product\Eav\Action\Full')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_model = new \Magento\Catalog\Model\Indexer\Product\Eav(
            $this->_productEavIndexerRow,
            $this->_productEavIndexerRows,
            $this->_productEavIndexerFull
        );
    }

    public function testExecuteAndExecuteList()
    {
        $ids = [1, 2, 3];
        $this->_productEavIndexerRow->expects($this->any())
            ->method('execute')
            ->with($ids);

        $this->_model->execute($ids);
        $this->_model->executeList($ids);
    }

    public function testExecuteFull()
    {
        $this->_productEavIndexerFull->expects($this->once())
            ->method('execute');

        $this->_model->executeFull();
    }

    public function testExecuteRow()
    {
        $id = 11;
        $this->_productEavIndexerRow->expects($this->once())
            ->method('execute')
            ->with($id);

        $this->_model->executeRow($id);
    }
}
