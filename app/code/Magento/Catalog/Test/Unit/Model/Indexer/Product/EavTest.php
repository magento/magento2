<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product;

class EavTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav
     */
    protected $model;

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

    /**
     * @var \Magento\Framework\Indexer\CacheContext|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheContextMock;

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

        $this->model = new \Magento\Catalog\Model\Indexer\Product\Eav(
            $this->_productEavIndexerRow,
            $this->_productEavIndexerRows,
            $this->_productEavIndexerFull
        );

        $this->cacheContextMock = $this->getMock(\Magento\Framework\Indexer\CacheContext::class, [], [], '', false);

        $cacheContextProperty = new \ReflectionProperty(
            \Magento\Catalog\Model\Indexer\Product\Eav::class,
            'cacheContext'
        );
        $cacheContextProperty->setAccessible(true);
        $cacheContextProperty->setValue($this->model, $this->cacheContextMock);
    }

    public function testExecute()
    {
        $ids = [1, 2, 3];
        $this->_productEavIndexerRow->expects($this->any())
            ->method('execute')
            ->with($ids);

        $this->cacheContextMock->expects($this->once())
            ->method('registerEntities')
            ->with(\Magento\Catalog\Model\Product::CACHE_TAG, $ids);

        $this->model->execute($ids);
    }

    public function testExecuteList()
    {
        $ids = [1, 2, 3];
        $this->_productEavIndexerRow->expects($this->any())
            ->method('execute')
            ->with($ids);

        $this->model->executeList($ids);
    }

    public function testExecuteFull()
    {
        $this->_productEavIndexerFull->expects($this->once())
            ->method('execute');

        $this->cacheContextMock->expects($this->once())
            ->method('registerTags')
            ->with(
                [
                    \Magento\Catalog\Model\Category::CACHE_TAG,
                    \Magento\Catalog\Model\Product::CACHE_TAG
                ]
            );

        $this->model->executeFull();
    }

    public function testExecuteRow()
    {
        $id = 11;
        $this->_productEavIndexerRow->expects($this->once())
            ->method('execute')
            ->with($id);

        $this->model->executeRow($id);
    }
}
