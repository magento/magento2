<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Product\Plugin;

use \Magento\Catalog\Model\Indexer\Category\Product\Plugin\StoreView;

class StoreViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Indexer\IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|
     */
    protected $pluginMock;

    /**
     * @var StoreView
     */
    protected $model;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    protected function setUp()
    {
        $this->indexerMock = $this->getMockForAbstractClass(
            'Magento\Framework\Indexer\IndexerInterface',
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getState', '__wakeup']
        );
        $this->subject = $this->getMock('Magento\Store\Model\ResourceModel\Group', [], [], '', false);
        $this->indexerRegistryMock = $this->getMock(
            'Magento\Framework\Indexer\IndexerRegistry',
            ['get'],
            [],
            '',
            false
        );

        $this->model = new StoreView($this->indexerRegistryMock);
    }

    public function testAroundSaveNewObject()
    {
        $this->mockIndexerMethods();
        $storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            ['isObjectNew', 'dataHasChangedFor', '__wakeup'],
            [],
            '',
            false
        );
        $storeMock->expects($this->once())->method('isObjectNew')->will($this->returnValue(true));
        $proceed = $this->mockPluginProceed();
        $this->assertFalse($this->model->aroundSave($this->subject, $proceed, $storeMock));
    }

    public function testAroundSaveHasChanged()
    {
        $this->mockIndexerMethods();
        $storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            ['isObjectNew', 'dataHasChangedFor', '__wakeup'],
            [],
            '',
            false
        );
        $storeMock->expects(
            $this->once()
        )->method(
            'dataHasChangedFor'
        )->with(
            'group_id'
        )->will(
            $this->returnValue(true)
        );
        $proceed = $this->mockPluginProceed();
        $this->assertFalse($this->model->aroundSave($this->subject, $proceed, $storeMock));
    }

    public function testAroundSaveNoNeed()
    {
        $storeMock = $this->getMock(
            'Magento\Store\Model\Store',
            ['isObjectNew', 'dataHasChangedFor', '__wakeup'],
            [],
            '',
            false
        );
        $storeMock->expects(
            $this->once()
        )->method(
            'dataHasChangedFor'
        )->with(
            'group_id'
        )->will(
            $this->returnValue(false)
        );
        $proceed = $this->mockPluginProceed();
        $this->assertFalse($this->model->aroundSave($this->subject, $proceed, $storeMock));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Indexer\Model\Indexer\State
     */
    protected function getStateMock()
    {
        $stateMock = $this->getMock(
            'Magento\Indexer\Model\Indexer\State',
            ['setStatus', 'save', '__wakeup'],
            [],
            '',
            false
        );
        $stateMock->expects($this->once())->method('setStatus')->with('invalid')->will($this->returnSelf());
        $stateMock->expects($this->once())->method('save')->will($this->returnSelf());

        return $stateMock;
    }

    protected function mockIndexerMethods()
    {
        $this->indexerMock->expects($this->once())->method('invalidate');
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID)
            ->will($this->returnValue($this->indexerMock));
    }

    protected function mockPluginProceed($returnValue = false)
    {
        return function () use ($returnValue) {
            return $returnValue;
        };
    }
}
