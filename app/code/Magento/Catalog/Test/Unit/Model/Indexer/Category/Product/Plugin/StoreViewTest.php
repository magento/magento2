<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Product\Plugin;

use Magento\Catalog\Model\Indexer\Category\Product\Plugin\StoreView;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Store\Model\Store;

class StoreViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Store|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var StoreView
     */
    protected $model;

    /**
     * @var IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    protected function setUp()
    {
        $this->indexerMock = $this->getMockForAbstractClass(
            IndexerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getState', '__wakeup']
        );
        $this->subject = $this->getMock(Group::class, [], [], '', false);
        $this->indexerRegistryMock = $this->getMock(
            IndexerRegistry::class,
            ['get'],
            [],
            '',
            false
        );
        $this->storeMock = $this->getMock(
            Store::class,
            ['isObjectNew', 'dataHasChangedFor', '__wakeup'],
            [],
            '',
            false
        );

        $this->model = new StoreView($this->indexerRegistryMock);
    }

    public function testAroundSaveNewObject()
    {
        $this->mockIndexerMethods();
        $this->storeMock->expects($this->once())->method('isObjectNew')->will($this->returnValue(true));
        $this->model->beforeSave($this->subject, $this->storeMock);
        $this->assertSame($this->subject, $this->model->afterSave($this->subject, $this->subject, $this->storeMock));
    }

    public function testAroundSaveHasChanged()
    {
        $this->mockIndexerMethods();
        $this->storeMock->expects($this->once())
            ->method('dataHasChangedFor')
            ->with('group_id')
            ->will($this->returnValue(true));
        $this->model->beforeSave($this->subject, $this->storeMock);
        $this->assertSame($this->subject, $this->model->afterSave($this->subject, $this->subject, $this->storeMock));
    }

    public function testAroundSaveNoNeed()
    {
        $this->storeMock->expects($this->once())
            ->method('dataHasChangedFor')
            ->with('group_id')
            ->will($this->returnValue(false)
        );
        $this->model->beforeSave($this->subject, $this->storeMock);
        $this->assertSame($this->subject, $this->model->afterSave($this->subject, $this->subject, $this->storeMock));
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
}
