<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
