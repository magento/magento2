<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Category\Flat\Plugin;

class StoreViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Indexer\Model\IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $stateMock;

    /**
     * @var StoreView
     */
    protected $model;

    /**
     * @var \Magento\Indexer\Model\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->indexerMock = $this->getMockForAbstractClass(
            'Magento\Indexer\Model\IndexerInterface',
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getState', '__wakeup']
        );
        $this->stateMock = $this->getMock(
            'Magento\Catalog\Model\Indexer\Category\Flat\State',
            ['isFlatEnabled'],
            [],
            '',
            false
        );
        $this->closureMock = function () {
            return false;
        };
        $this->subjectMock = $this->getMock('Magento\Store\Model\Resource\Store', [], [], '', false);
        $this->indexerRegistryMock = $this->getMock('Magento\Indexer\Model\IndexerRegistry', ['get'], [], '', false);
        $this->model = new StoreView($this->indexerRegistryMock, $this->stateMock);
    }

    public function testAroundSaveNewObject()
    {
        $this->mockConfigFlatEnabled();
        $this->mockIndexerMethods();
        $storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            ['isObjectNew', 'dataHasChangedFor', '__wakeup'],
            [],
            '',
            false
        );
        $storeMock->expects($this->once())->method('isObjectNew')->will($this->returnValue(true));
        $this->assertFalse($this->model->aroundSave($this->subjectMock, $this->closureMock, $storeMock));
    }

    public function testAroundSaveHasChanged()
    {
        $storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            ['isObjectNew', 'dataHasChangedFor', '__wakeup'],
            [],
            '',
            false
        );
        $this->assertFalse($this->model->aroundSave($this->subjectMock, $this->closureMock, $storeMock));
    }

    public function testAroundSaveNoNeed()
    {
        $this->mockConfigFlatEnabledNeever();
        $storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            ['isObjectNew', 'dataHasChangedFor', '__wakeup'],
            [],
            '',
            false
        );
        $this->assertFalse($this->model->aroundSave($this->subjectMock, $this->closureMock, $storeMock));
    }

    protected function mockIndexerMethods()
    {
        $this->indexerMock->expects($this->once())->method('invalidate');
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Catalog\Model\Indexer\Category\Flat\State::INDEXER_ID)
            ->will($this->returnValue($this->indexerMock));
    }

    protected function mockConfigFlatEnabled()
    {
        $this->stateMock->expects($this->once())->method('isFlatEnabled')->will($this->returnValue(true));
    }

    protected function mockConfigFlatEnabledNeever()
    {
        $this->stateMock->expects($this->never())->method('isFlatEnabled');
    }
}
