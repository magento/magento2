<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Model;

use Magento\Framework\Indexer\StateInterface;
use Magento\Indexer\Model\Indexer\State;

class IndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Model\Indexer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var \Magento\Framework\Indexer\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Framework\Indexer\ActionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionFactoryMock;

    /**
     * @var \Magento\Framework\Mview\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\Indexer\Model\Indexer\StateFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stateFactoryMock;

    /**
     * @var \Magento\Indexer\Model\Indexer\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexFactoryMock;

    protected function setUp()
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
        $this->actionFactoryMock = $this->getMock(
            \Magento\Framework\Indexer\ActionFactory::class,
            ['create'],
            [],
            '',
            false
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
        $this->stateFactoryMock = $this->getMock(
            \Magento\Indexer\Model\Indexer\StateFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->indexFactoryMock = $this->getMock(
            \Magento\Indexer\Model\Indexer\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage indexer_id indexer does not exist.
     */
    public function testLoadWithException()
    {
        $indexId = 'indexer_id';
        $this->configMock->expects(
            $this->once()
        )->method(
            'getIndexer'
        )->with(
            $indexId
        )->will(
            $this->returnValue($this->getIndexerData())
        );
        $this->model->load($indexId);
    }

    public function testGetView()
    {
        $indexId = 'indexer_internal_name';
        $this->viewMock->expects($this->once())->method('load')->with('view_test')->will($this->returnSelf());
        $this->loadIndexer($indexId);

        $this->assertEquals($this->viewMock, $this->model->getView());
    }

    public function testGetState()
    {
        $indexId = 'indexer_internal_name';
        $stateMock = $this->getMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['loadByIndexer', 'getId', '__wakeup'],
            [],
            '',
            false
        );
        $stateMock->expects($this->once())->method('loadByIndexer')->with($indexId)->will($this->returnSelf());
        $this->stateFactoryMock->expects($this->once())->method('create')->will($this->returnValue($stateMock));

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

        $this->viewMock->expects($this->any())->method('getId')->will($this->returnValue(1));
        $this->viewMock->expects($this->once())->method('isEnabled')->will($this->returnValue($getViewIsEnabled));
        $this->viewMock->expects($this->any())->method('getUpdated')->will($this->returnValue($getViewGetUpdated));

        $stateMock = $this->getMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['load', 'getId', 'setIndexerId', '__wakeup', 'getUpdated'],
            [],
            '',
            false
        );

        $stateMock->expects($this->any())->method('getUpdated')->will($this->returnValue($getStateGetUpdated));
        $this->stateFactoryMock->expects($this->once())->method('create')->will($this->returnValue($stateMock));

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
            $this->assertEquals($getStateGetUpdated, $this->model->getLatestUpdated());
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
            [true, '06-Jan-1944', '05-Jan-1944']
        ];
    }

    public function testReindexAll()
    {
        $indexId = 'indexer_internal_name';
        $this->loadIndexer($indexId);

        $stateMock = $this->getMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['load', 'getId', 'setIndexerId', '__wakeup', 'getStatus', 'setStatus', 'save'],
            [],
            '',
            false
        );
        $stateMock->expects($this->once())->method('load')->with($indexId, 'indexer_id')->will($this->returnSelf());
        $stateMock->expects($this->never())->method('setIndexerId');
        $stateMock->expects($this->once())->method('getId')->will($this->returnValue(1));
        $stateMock->expects($this->exactly(2))->method('setStatus')->will($this->returnSelf());
        $stateMock->expects($this->once())->method('getStatus')->will($this->returnValue('idle'));
        $stateMock->expects($this->exactly(2))->method('save')->will($this->returnSelf());
        $this->stateFactoryMock->expects($this->once())->method('create')->will($this->returnValue($stateMock));

        $this->viewMock->expects($this->once())->method('isEnabled')->will($this->returnValue(true));
        $this->viewMock->expects($this->once())->method('suspend');
        $this->viewMock->expects($this->once())->method('resume');

        $actionMock = $this->getMock(
            \Magento\Framework\Indexer\ActionInterface::class,
            ['executeFull', 'executeList', 'executeRow'],
            [],
            '',
            false
        );
        $this->actionFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Some\Class\Name'
        )->will(
            $this->returnValue($actionMock)
        );

        $this->model->reindexAll();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Test exception
     */
    public function testReindexAllWithException()
    {
        $indexId = 'indexer_internal_name';
        $this->loadIndexer($indexId);

        $stateMock = $this->getMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['load', 'getId', 'setIndexerId', '__wakeup', 'getStatus', 'setStatus', 'save'],
            [],
            '',
            false
        );
        $stateMock->expects($this->once())->method('load')->with($indexId, 'indexer_id')->will($this->returnSelf());
        $stateMock->expects($this->never())->method('setIndexerId');
        $stateMock->expects($this->once())->method('getId')->will($this->returnValue(1));
        $stateMock->expects($this->exactly(2))->method('setStatus')->will($this->returnSelf());
        $stateMock->expects($this->once())->method('getStatus')->will($this->returnValue('idle'));
        $stateMock->expects($this->exactly(2))->method('save')->will($this->returnSelf());
        $this->stateFactoryMock->expects($this->once())->method('create')->will($this->returnValue($stateMock));

        $this->viewMock->expects($this->once())->method('isEnabled')->will($this->returnValue(false));
        $this->viewMock->expects($this->never())->method('suspend');
        $this->viewMock->expects($this->once())->method('resume');

        $actionMock = $this->getMock(
            \Magento\Framework\Indexer\ActionInterface::class,
            ['executeFull', 'executeList', 'executeRow'],
            [],
            '',
            false
        );
        $actionMock->expects($this->once())->method('executeFull')->will(
            $this->returnCallback(
                function () {
                    throw new \Exception('Test exception');
                }
            )
        );
        $this->actionFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Some\Class\Name'
        )->will(
            $this->returnValue($actionMock)
        );

        $this->model->reindexAll();
    }

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
        )->will(
            $this->returnValue($this->getIndexerData())
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
        $stateMock = $this->getMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['loadByIndexer', 'getId', '__wakeup'],
            [],
            '',
            false
        );

        $this->model->setState($stateMock);
        $this->assertInstanceOf(\Magento\Indexer\Model\Indexer\State::class, $this->model->getState());
    }

    public function testIsScheduled()
    {
        $result = true;
        $this->viewMock->expects($this->once())->method('load')->will($this->returnSelf());
        $this->viewMock->expects($this->once())->method('isEnabled')->will($this->returnValue($result));
        $this->assertEquals($result, $this->model->isScheduled());
    }

    /**
     * @param bool $scheduled
     * @param string $method
     * @dataProvider setScheduledDataProvider
     */
    public function testSetScheduled($scheduled, $method)
    {
        $stateMock = $this->getMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['load', 'save'],
            [],
            '',
            false
        );

        $this->stateFactoryMock->expects($this->once())->method('create')->will($this->returnValue($stateMock));
        $this->viewMock->expects($this->once())->method('load')->will($this->returnSelf());
        $this->viewMock->expects($this->once())->method($method)->will($this->returnValue(true));
        $stateMock->expects($this->once())->method('save')->will($this->returnSelf());
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
        $stateMock = $this->getMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['load', 'getStatus'],
            [],
            '',
            false
        );

        $this->stateFactoryMock->expects($this->once())->method('create')->will($this->returnValue($stateMock));
        $stateMock->expects($this->once())->method('getStatus')->will($this->returnValue($status));
        $this->assertEquals($status, $this->model->getStatus());
    }

    /**
     * @param string $method
     * @param string $status
     * @dataProvider statusDataProvider
     */
    public function testStatus($method, $status)
    {
        $stateMock = $this->getMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['load', 'getStatus'],
            [],
            '',
            false
        );

        $this->stateFactoryMock->expects($this->once())->method('create')->will($this->returnValue($stateMock));
        $stateMock->expects($this->once())->method('getStatus')->will($this->returnValue($status));
        $this->assertEquals(true, $this->model->$method());
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
        $stateMock = $this->getMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['load', 'setStatus', 'save'],
            [],
            '',
            false
        );

        $this->stateFactoryMock->expects($this->once())->method('create')->will($this->returnValue($stateMock));
        $stateMock->expects($this->once())->method('setStatus')->with(StateInterface::STATUS_INVALID)->will(
            $this->returnSelf()
        );
        $stateMock->expects($this->once())->method('save')->will($this->returnSelf());
        $this->model->invalidate();
    }

    public function testReindexRow()
    {
        $id = 1;

        $stateMock = $this->getMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['load', 'save'],
            [],
            '',
            false
        );
        $actionMock = $this->getMock(
            \Magento\Framework\Indexer\ActionInterface::class,
            ['executeFull', 'executeList', 'executeRow'],
            [],
            '',
            false
        );

        $this->actionFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($actionMock)
        );

        $this->stateFactoryMock->expects($this->once())->method('create')->will($this->returnValue($stateMock));
        $stateMock->expects($this->once())->method('save')->will($this->returnSelf());
        $actionMock->expects($this->once())->method('executeRow')->with($id)->will($this->returnSelf());
        $this->model->reindexRow($id);
    }

    public function testReindexList()
    {
        $ids = [1];

        $stateMock = $this->getMock(
            \Magento\Indexer\Model\Indexer\State::class,
            ['load', 'save'],
            [],
            '',
            false
        );
        $actionMock = $this->getMock(
            \Magento\Framework\Indexer\ActionInterface::class,
            ['executeFull', 'executeList', 'executeRow'],
            [],
            '',
            false
        );

        $this->actionFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->will(
            $this->returnValue($actionMock)
        );

        $this->stateFactoryMock->expects($this->once())->method('create')->will($this->returnValue($stateMock));
        $stateMock->expects($this->once())->method('save')->will($this->returnSelf());
        $actionMock->expects($this->once())->method('executeList')->with($ids)->will($this->returnSelf());
        $this->model->reindexList($ids);
    }
}
