<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model;

use Magento\Framework\Indexer\StateInterface;

class IndexerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Indexer\Model\Indexer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $model;

    /**
     * @var \Magento\Framework\Indexer\ConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Framework\Indexer\ActionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $actionFactoryMock;

    /**
     * @var \Magento\Framework\Mview\ViewInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\Indexer\Model\Indexer\StateFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stateFactoryMock;

    /**
     * @var \Magento\Indexer\Model\Indexer\CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $indexFactoryMock;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockForAbstractClass(
            \Magento\Framework\Indexer\ConfigInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getIndexer']
        );
        $this->actionFactoryMock = $this->createPartialMock(
            \Magento\Framework\Indexer\ActionFactory::class,
            ['create']
        );
        $this->viewMock = $this->getMockForAbstractClass(
            \Magento\Framework\Mview\ViewInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['load', 'isEnabled', 'getUpdated', 'getStatus', '__wakeup', 'getId', 'suspend', 'resume']
        );
        $this->stateFactoryMock = $this->createPartialMock(
            \Magento\Indexer\Model\Indexer\StateFactory::class,
            ['create']
        );
        $this->indexFactoryMock = $this->createPartialMock(
            \Magento\Indexer\Model\Indexer\CollectionFactory::class,
            ['create']
        );
        $structureFactory = $this->getMockBuilder(\Magento\Framework\Indexer\StructureFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        /** @var \Magento\Framework\Indexer\StructureFactory $structureFactory */
        $this->model = new \Magento\Indexer\Model\Indexer(
            $this->configMock,
            $this->actionFactoryMock,
            $structureFactory,
            $this->viewMock,
            $this->stateFactoryMock,
            $this->indexFactoryMock
        );
    }

    /**
     */
    public function testLoadWithException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('indexer_id indexer does not exist.');

        $indexId = 'indexer_id';
        $this->configMock->expects(
            $this->once()
        )->method(
            'getIndexer'
        )->with(
            $indexId
        )->willReturn(
            $this->getIndexerData()
        );
        $this->model->load($indexId);
    }

    public function testGetView()
    {
        $indexId = 'indexer_internal_name';
        $this->viewMock->expects($this->once())->method('load')->with('view_test')->willReturnSelf();
        $this->loadIndexer($indexId);

        $this->assertEquals($this->viewMock, $this->model->getView());
    }

    public function testGetState()
    {
        $indexId = 'indexer_internal_name';
        $stateMock = $this->createPartialMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['loadByIndexer', 'getId', '__wakeup']
        );
        $stateMock->expects($this->once())->method('loadByIndexer')->with($indexId)->willReturnSelf();
        $this->stateFactoryMock->expects($this->once())->method('create')->willReturn($stateMock);

        $this->loadIndexer($indexId);

        $this->assertInstanceOf(\Magento\Indexer\Model\Indexer\State::class, $this->model->getState());
    }

    /**
     * @param bool $getViewIsEnabled
     * @param string $getViewGetUpdated
     * @param string $getStateGetUpdated
     * @dataProvider getLatestUpdatedDataProvider
     */
    public function testGetLatestUpdated($getViewIsEnabled, $getViewGetUpdated, $getStateGetUpdated)
    {
        $indexId = 'indexer_internal_name';
        $this->loadIndexer($indexId);

        $this->viewMock->expects($this->any())->method('getId')->willReturn(1);
        $this->viewMock->expects($this->once())->method('isEnabled')->willReturn($getViewIsEnabled);
        $this->viewMock->expects($this->any())->method('getUpdated')->willReturn($getViewGetUpdated);

        $stateMock = $this->createPartialMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['load', 'getId', 'setIndexerId', '__wakeup', 'getUpdated']
        );

        $stateMock->expects($this->any())->method('getUpdated')->willReturn($getStateGetUpdated);
        $this->stateFactoryMock->expects($this->once())->method('create')->willReturn($stateMock);

        if ($getViewIsEnabled && $getViewGetUpdated) {
            if (!$getStateGetUpdated) {
                $this->assertEquals($getViewGetUpdated, $this->model->getLatestUpdated());
            } else {
                if ($getViewGetUpdated == $getStateGetUpdated) {
                    $this->assertEquals($getViewGetUpdated, $this->model->getLatestUpdated());
                } else {
                    $this->assertEquals($getViewGetUpdated, $this->model->getLatestUpdated());
                }
            }
        } else {
            $getLatestUpdated = $this->model->getLatestUpdated();
            $this->assertEquals($getStateGetUpdated, $getLatestUpdated);

            if ($getStateGetUpdated === null) {
                $this->assertNotNull($getLatestUpdated);
            }
        }
    }

    /**
     * @return array
     */
    public function getLatestUpdatedDataProvider()
    {
        return [
            [false, '06-Jan-1944', '06-Jan-1944'],
            [false, '', '06-Jan-1944'],
            [false, '06-Jan-1944', ''],
            [false, '', ''],
            [true, '06-Jan-1944', '06-Jan-1944'],
            [true, '', '06-Jan-1944'],
            [true, '06-Jan-1944', ''],
            [true, '', ''],
            [true, '06-Jan-1944', '05-Jan-1944'],
            [false, null, null],
        ];
    }

    public function testReindexAll()
    {
        $indexId = 'indexer_internal_name';
        $this->loadIndexer($indexId);

        $stateMock = $this->createPartialMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['load', 'getId', 'setIndexerId', '__wakeup', 'getStatus', 'setStatus', 'save']
        );
        $stateMock->expects($this->once())->method('load')->with($indexId, 'indexer_id')->willReturnSelf();
        $stateMock->expects($this->never())->method('setIndexerId');
        $stateMock->expects($this->once())->method('getId')->willReturn(1);
        $stateMock->expects($this->exactly(2))->method('setStatus')->willReturnSelf();
        $stateMock->expects($this->once())->method('getStatus')->willReturn('idle');
        $stateMock->expects($this->exactly(2))->method('save')->willReturnSelf();
        $this->stateFactoryMock->expects($this->once())->method('create')->willReturn($stateMock);

        $this->viewMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->viewMock->expects($this->once())->method('suspend');
        $this->viewMock->expects($this->once())->method('resume');

        $actionMock = $this->createPartialMock(
            \Magento\Framework\Indexer\ActionInterface::class,
            ['executeFull', 'executeList', 'executeRow']
        );
        $this->actionFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Some\Class\Name'
        )->willReturn(
            $actionMock
        );

        $this->model->reindexAll();
    }

    /**
     */
    public function testReindexAllWithException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test exception');

        $indexId = 'indexer_internal_name';
        $this->loadIndexer($indexId);

        $stateMock = $this->createPartialMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['load', 'getId', 'setIndexerId', '__wakeup', 'getStatus', 'setStatus', 'save']
        );
        $stateMock->expects($this->once())->method('load')->with($indexId, 'indexer_id')->willReturnSelf();
        $stateMock->expects($this->never())->method('setIndexerId');
        $stateMock->expects($this->once())->method('getId')->willReturn(1);
        $stateMock->expects($this->exactly(2))->method('setStatus')->willReturnSelf();
        $stateMock->expects($this->once())->method('getStatus')->willReturn('idle');
        $stateMock->expects($this->exactly(2))->method('save')->willReturnSelf();
        $this->stateFactoryMock->expects($this->once())->method('create')->willReturn($stateMock);

        $this->viewMock->expects($this->once())->method('isEnabled')->willReturn(false);
        $this->viewMock->expects($this->never())->method('suspend');
        $this->viewMock->expects($this->once())->method('resume');

        $actionMock = $this->createPartialMock(
            \Magento\Framework\Indexer\ActionInterface::class,
            ['executeFull', 'executeList', 'executeRow']
        );
        $actionMock->expects($this->once())->method('executeFull')->willReturnCallback(
            
                function () {
                    throw new \Exception('Test exception');
                }
            
        );
        $this->actionFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Some\Class\Name'
        )->willReturn(
            $actionMock
        );

        $this->model->reindexAll();
    }

    /**
     */
    public function testReindexAllWithError()
    {
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Test Engine Error');


        $indexId = 'indexer_internal_name';
        $this->loadIndexer($indexId);

        $stateMock = $this->createPartialMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['load', 'getId', 'setIndexerId', '__wakeup', 'getStatus', 'setStatus', 'save']
        );
        $stateMock->expects($this->once())->method('load')->with($indexId, 'indexer_id')->willReturnSelf();
        $stateMock->expects($this->never())->method('setIndexerId');
        $stateMock->expects($this->once())->method('getId')->willReturn(1);
        $stateMock->expects($this->exactly(2))->method('setStatus')->willReturnSelf();
        $stateMock->expects($this->once())->method('getStatus')->willReturn('idle');
        $stateMock->expects($this->exactly(2))->method('save')->willReturnSelf();
        $this->stateFactoryMock->expects($this->once())->method('create')->willReturn($stateMock);

        $this->viewMock->expects($this->once())->method('isEnabled')->willReturn(false);
        $this->viewMock->expects($this->never())->method('suspend');
        $this->viewMock->expects($this->once())->method('resume');

        $actionMock = $this->createPartialMock(
            \Magento\Framework\Indexer\ActionInterface::class,
            ['executeFull', 'executeList', 'executeRow']
        );
        $actionMock->expects($this->once())->method('executeFull')->willReturnCallback(
            
                function () {
                     throw new \Error('Test Engine Error');
                }
            
        );
        $this->actionFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Some\Class\Name'
        )->willReturn(
            $actionMock
        );

        $this->model->reindexAll();
    }

    /**
     * @return array
     */
    protected function getIndexerData()
    {
        return [
            'indexer_id' => 'indexer_internal_name',
            'view_id' => 'view_test',
            'action_class' => 'Some\Class\Name',
            'title' => 'Indexer public name',
            'description' => 'Indexer public description'
        ];
    }

    /**
     * @param $indexId
     */
    protected function loadIndexer($indexId)
    {
        $this->configMock->expects(
            $this->once()
        )->method(
            'getIndexer'
        )->with(
            $indexId
        )->willReturn(
            $this->getIndexerData()
        );
        $this->model->load($indexId);
    }

    public function testGetTitle()
    {
        $result = 'Test Result';
        $this->model->setTitle($result);
        $this->assertEquals($result, $this->model->getTitle());
    }

    public function testGetDescription()
    {
        $result = 'Test Result';
        $this->model->setDescription($result);
        $this->assertEquals($result, $this->model->getDescription());
    }

    public function testSetState()
    {
        $stateMock = $this->createPartialMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['loadByIndexer', 'getId', '__wakeup']
        );

        $this->model->setState($stateMock);
        $this->assertInstanceOf(\Magento\Indexer\Model\Indexer\State::class, $this->model->getState());
    }

    public function testIsScheduled()
    {
        $result = true;
        $this->viewMock->expects($this->once())->method('load')->willReturnSelf();
        $this->viewMock->expects($this->once())->method('isEnabled')->willReturn($result);
        $this->assertEquals($result, $this->model->isScheduled());
    }

    /**
     * @param bool $scheduled
     * @param string $method
     * @dataProvider setScheduledDataProvider
     */
    public function testSetScheduled($scheduled, $method)
    {
        $stateMock = $this->createPartialMock(\Magento\Indexer\Model\Indexer\State::class, ['load', 'save']);

        $this->stateFactoryMock->expects($this->once())->method('create')->willReturn($stateMock);
        $this->viewMock->expects($this->once())->method('load')->willReturnSelf();
        $this->viewMock->expects($this->once())->method($method)->willReturn(true);
        $stateMock->expects($this->once())->method('save')->willReturnSelf();
        $this->model->setScheduled($scheduled);
    }

    /**
     * @return array
     */
    public function setScheduledDataProvider()
    {
        return [
            [true, 'subscribe'],
            [false, 'unsubscribe']
        ];
    }

    public function testGetStatus()
    {
        $status = StateInterface::STATUS_WORKING;
        $stateMock = $this->createPartialMock(\Magento\Indexer\Model\Indexer\State::class, ['load', 'getStatus']);

        $this->stateFactoryMock->expects($this->once())->method('create')->willReturn($stateMock);
        $stateMock->expects($this->once())->method('getStatus')->willReturn($status);
        $this->assertEquals($status, $this->model->getStatus());
    }

    /**
     * @param string $method
     * @param string $status
     * @dataProvider statusDataProvider
     */
    public function testStatus($method, $status)
    {
        $stateMock = $this->createPartialMock(\Magento\Indexer\Model\Indexer\State::class, ['load', 'getStatus']);

        $this->stateFactoryMock->expects($this->once())->method('create')->willReturn($stateMock);
        $stateMock->expects($this->once())->method('getStatus')->willReturn($status);
        $this->assertTrue($this->model->$method());
    }

    /**
     * @return array
     */
    public function statusDataProvider()
    {
        return [
            ['isValid', StateInterface::STATUS_VALID],
            ['isInvalid', StateInterface::STATUS_INVALID],
            ['isWorking', StateInterface::STATUS_WORKING]
        ];
    }

    public function testInvalidate()
    {
        $stateMock = $this->createPartialMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['load', 'setStatus', 'save']
        );

        $this->stateFactoryMock->expects($this->once())->method('create')->willReturn($stateMock);
        $stateMock->expects($this->once())->method('setStatus')->with(StateInterface::STATUS_INVALID)->willReturnSelf(
            
        );
        $stateMock->expects($this->once())->method('save')->willReturnSelf();
        $this->model->invalidate();
    }

    public function testReindexRow()
    {
        $id = 1;

        $stateMock = $this->createPartialMock(\Magento\Indexer\Model\Indexer\State::class, ['load', 'save']);
        $actionMock = $this->createPartialMock(
            \Magento\Framework\Indexer\ActionInterface::class,
            ['executeFull', 'executeList', 'executeRow']
        );

        $this->actionFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->willReturn(
            $actionMock
        );

        $this->stateFactoryMock->expects($this->once())->method('create')->willReturn($stateMock);
        $stateMock->expects($this->once())->method('save')->willReturnSelf();
        $actionMock->expects($this->once())->method('executeRow')->with($id)->willReturnSelf();
        $this->model->reindexRow($id);
    }

    public function testReindexList()
    {
        $ids = [1];

        $stateMock = $this->createPartialMock(\Magento\Indexer\Model\Indexer\State::class, ['load', 'save']);
        $actionMock = $this->createPartialMock(
            \Magento\Framework\Indexer\ActionInterface::class,
            ['executeFull', 'executeList', 'executeRow']
        );

        $this->actionFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->willReturn(
            $actionMock
        );

        $this->stateFactoryMock->expects($this->once())->method('create')->willReturn($stateMock);
        $stateMock->expects($this->once())->method('save')->willReturnSelf();
        $actionMock->expects($this->once())->method('executeList')->with($ids)->willReturnSelf();
        $this->model->reindexList($ids);
    }
}
