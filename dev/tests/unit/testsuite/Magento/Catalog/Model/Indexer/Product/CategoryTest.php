<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product;

class CategoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Product
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Product\Action\FullFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fullMock;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Category\Action\RowsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rowsMock;

    /**
     * @var \Magento\Indexer\Model\IndexerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerMock;

    /**
     * @var \Magento\Indexer\Model\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    protected function setUp()
    {
        $this->fullMock = $this->getMock(
            'Magento\Catalog\Model\Indexer\Category\Product\Action\FullFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->rowsMock = $this->getMock(
            'Magento\Catalog\Model\Indexer\Product\Category\Action\RowsFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->indexerMock = $this->getMockForAbstractClass(
            'Magento\Indexer\Model\IndexerInterface',
            [],
            '',
            false,
            false,
            true,
            ['getId', 'load', 'isInvalid', 'isWorking', '__wakeup']
        );

        $this->indexerRegistryMock = $this->getMock('Magento\Indexer\Model\IndexerRegistry', ['get'], [], '', false);

        $this->model = new \Magento\Catalog\Model\Indexer\Product\Category(
            $this->fullMock,
            $this->rowsMock,
            $this->indexerRegistryMock
        );
    }

    public function testExecuteWithIndexerWorking()
    {
        $ids = [1, 2, 3];

        $this->indexerMock->expects($this->once())->method('isWorking')->will($this->returnValue(true));
        $this->prepareIndexer();

        $rowMock = $this->getMock(
            'Magento\Catalog\Model\Indexer\Product\Category\Action\Rows',
            ['execute'],
            [],
            '',
            false
        );
        $rowMock->expects($this->at(0))->method('execute')->with($ids, true)->will($this->returnSelf());
        $rowMock->expects($this->at(1))->method('execute')->with($ids, false)->will($this->returnSelf());

        $this->rowsMock->expects($this->once())->method('create')->will($this->returnValue($rowMock));

        $this->model->execute($ids);
    }

    public function testExecuteWithIndexerNotWorking()
    {
        $ids = [1, 2, 3];

        $this->indexerMock->expects($this->once())->method('isWorking')->will($this->returnValue(false));
        $this->prepareIndexer();

        $rowMock = $this->getMock(
            'Magento\Catalog\Model\Indexer\Product\Category\Action\Rows',
            ['execute'],
            [],
            '',
            false
        );
        $rowMock->expects($this->once())->method('execute')->with($ids, false)->will($this->returnSelf());

        $this->rowsMock->expects($this->once())->method('create')->will($this->returnValue($rowMock));

        $this->model->execute($ids);
    }

    protected function prepareIndexer()
    {
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID)
            ->will($this->returnValue($this->indexerMock));
    }
}
