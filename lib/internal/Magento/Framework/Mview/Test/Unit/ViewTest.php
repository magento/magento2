<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\Test\Unit;

use Magento\Framework\Mview\ActionFactory;
use Magento\Framework\Mview\ActionInterface;
use Magento\Framework\Mview\ConfigInterface;
use \Magento\Framework\Mview\View;
use Magento\Framework\Mview\View\Changelog;
use Magento\Framework\Mview\View\StateInterface;
use Magento\Framework\Mview\View\Subscription;
use Magento\Framework\Mview\View\SubscriptionFactory;
use Magento\Indexer\Model\Mview\View\State;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class to test Mview functionality
 */
class ViewTest extends TestCase
{
    /**
     * @var View
     */
    protected $model;

    /**
     * @var MockObject|ConfigInterface
     */
    protected $configMock;

    /**
     * @var MockObject|ActionFactory
     */
    protected $actionFactoryMock;

    /**
     * @var MockObject|State
     */
    protected $stateMock;

    /**
     * @var MockObject|Changelog
     */
    protected $changelogMock;

    /**
     * @var MockObject|SubscriptionFactory
     */
    protected $subscriptionFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockForAbstractClass(
            ConfigInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getView']
        );
        $this->actionFactoryMock = $this->createPartialMock(ActionFactory::class, ['get']);
        $this->stateMock = $this->createPartialMock(
            State::class,
            [
                'getViewId',
                'loadByView',
                'getVersionId',
                'setVersionId',
                'getUpdated',
                'getStatus',
                'setStatus',
                'getMode',
                'setMode',
                'save',
                '__wakeup',
            ]
        );
        $this->changelogMock = $this->createPartialMock(
            Changelog::class,
            ['getViewId', 'setViewId', 'create', 'drop', 'getVersion', 'getList', 'clear']
        );
        $this->subscriptionFactoryMock = $this->createPartialMock(
            SubscriptionFactory::class,
            ['create']
        );
        $this->model = new View(
            $this->configMock,
            $this->actionFactoryMock,
            $this->stateMock,
            $this->changelogMock,
            $this->subscriptionFactoryMock
        );
    }

    /**
     * Test to Return view action class
     */
    public function testGetActionClass()
    {
        $this->model->setData('action_class', 'actionClass');
        $this->assertEquals('actionClass', $this->model->getActionClass());
    }

    /**
     * Test to Return view group
     */
    public function testGetGroup()
    {
        $this->model->setData('group', 'some_group');
        $this->assertEquals('some_group', $this->model->getGroup());
    }

    /**
     * Test to Return view subscriptions
     */
    public function testGetSubscriptions()
    {
        $this->model->setData('subscriptions', ['subscription']);
        $this->assertEquals(['subscription'], $this->model->getSubscriptions());
    }

    /**
     * Test to Fill view data from config
     */
    public function testLoad()
    {
        $viewId = 'view_test';
        $this->configMock->expects(
            $this->once()
        )->method(
            'getView'
        )->with(
            $viewId
        )->willReturn(
            $this->getViewData()
        );
        $this->assertInstanceOf(View::class, $this->model->load($viewId));
    }

    /**
     * Test to Fill view data from config
     *
     */
    public function testLoadWithException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('view_id view does not exist.');

        $viewId = 'view_id';
        $this->configMock->expects(
            $this->once()
        )->method(
            'getView'
        )->with(
            $viewId
        )->willReturn(
            $this->getViewData()
        );
        $this->model->load($viewId);
    }

    /**
     * Test to Create subscriptions
     */
    public function testSubscribe()
    {
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(StateInterface::MODE_DISABLED);
        $this->stateMock->expects($this->once())
            ->method('setMode')
            ->with(StateInterface::MODE_ENABLED)
            ->willReturnSelf();
        $this->changelogMock->expects($this->once())
            ->method('create');
        $subscriptionMock = $this->createPartialMock(Subscription::class, ['create']);
        $subscriptionMock->expects($this->exactly(1))->method('create');
        $this->subscriptionFactoryMock->expects(
            $this->exactly(1)
        )->method(
            'create'
        )->willReturn(
            $subscriptionMock
        );
        $this->loadView();
        $this->model->subscribe();
    }

    /**
     * Test to Create subscriptions
     */
    public function testSubscribeEnabled()
    {
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(StateInterface::MODE_ENABLED);
        $this->stateMock->expects($this->never())
            ->method('setMode');
        $this->changelogMock->expects($this->never())
            ->method('create');
        $this->subscriptionFactoryMock->expects($this->never())
            ->method('create');
        $this->loadView();
        $this->model->subscribe();
    }

    /**
     */
    public function testSubscribeWithException()
    {
        $this->expectException(\Exception::class);

        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(StateInterface::MODE_DISABLED);

        $this->changelogMock->expects($this->once())
            ->method('create')
            ->willReturnCallback(
                function () {
                    throw new \Exception();
                }
            );

        $this->loadView();
        $this->model->subscribe();
    }

    /**
     * Test to Remove subscriptions
     */
    public function testUnsubscribe()
    {
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(StateInterface::MODE_ENABLED);
        $this->stateMock->expects($this->once())
            ->method('setMode')
            ->with(StateInterface::MODE_DISABLED)
            ->willReturnSelf();
        $this->changelogMock->expects($this->never())
            ->method('drop');
        $subscriptionMock = $this->createPartialMock(Subscription::class, ['remove']);
        $subscriptionMock->expects($this->exactly(1))->method('remove');
        $this->subscriptionFactoryMock->expects(
            $this->exactly(1)
        )->method(
            'create'
        )->willReturn(
            $subscriptionMock
        );
        $this->loadView();
        $this->model->unsubscribe();
    }

    /**
     * Test to Remove subscriptions
     */
    public function testUnsubscribeDisabled()
    {
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(StateInterface::MODE_DISABLED);
        $this->stateMock->expects($this->never())
            ->method('setVersionId');
        $this->stateMock->expects($this->never())
            ->method('setMode');
        $this->changelogMock->expects($this->never())
            ->method('drop');
        $this->subscriptionFactoryMock->expects($this->never())
            ->method('create');
        $this->loadView();
        $this->model->unsubscribe();
    }

    /**
     */
    public function testUnsubscribeWithException()
    {
        $this->expectException(\Exception::class);

        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(StateInterface::MODE_ENABLED);

        $subscriptionMock = $this->createPartialMock(Subscription::class, ['remove']);
        $subscriptionMock->expects($this->exactly(1))
            ->method('remove')
            ->willReturnCallback(
                function () {
                    throw new \Exception();
                }
            );
        $this->subscriptionFactoryMock->expects($this->exactly(1))
            ->method('create')
            ->willReturn($subscriptionMock);

        $this->loadView();
        $this->model->unsubscribe();
    }

    /**
     * Test to Materialize view by IDs in changelog
     */
    public function testUpdate()
    {
        $currentVersionId = 3;
        $lastVersionId = 1;
        $listId = [2, 3];

        $this->stateMock->expects($this->any())
            ->method('getViewId')
            ->willReturn(1);
        $this->stateMock->expects($this->once())
            ->method('getVersionId')
            ->willReturn($lastVersionId);
        $this->stateMock->expects($this->once())
            ->method('setVersionId')
            ->willReturnSelf();
        $this->stateMock->expects($this->atLeastOnce())
            ->method('getMode')
            ->willReturn(StateInterface::MODE_ENABLED);
        $this->stateMock->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn(StateInterface::STATUS_IDLE);
        $this->stateMock->expects($this->exactly(2))
            ->method('setStatus')
            ->willReturnSelf();
        $this->stateMock->expects($this->exactly(2))
            ->method('save')
            ->willReturnSelf();

        $this->changelogMock->expects(
            $this->once()
        )->method(
            'getVersion'
        )->willReturn(
            $currentVersionId
        );
        $this->changelogMock->expects(
            $this->once()
        )->method(
            'getList'
        )->with(
            $lastVersionId,
            $currentVersionId
        )->willReturn(
            $listId
        );

        $actionMock = $this->getMockForAbstractClass(ActionInterface::class);
        $actionMock->expects($this->once())->method('execute')->with($listId)->willReturnSelf();
        $this->actionFactoryMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'Some\Class\Name'
        )->willReturn(
            $actionMock
        );

        $this->loadView();
        $this->model->update();
    }

    /**
     * Test to Materialize view by IDs in changelog
     */
    public function testUpdateEx(): void
    {
        $currentVersionId = 200100;
        $lastVersionId = 1;
        $listIdBatchOne = $this->generateChangeLog(100000, 1, 100);
        $listIdBatchTwo = $this->generateChangeLog(100000, 1, 50);
        $listIdBatchThree = $this->generateChangeLog(100, 100, 150);

        $this->stateMock->method('getViewId')->willReturn(1);
        $this->stateMock->method('getVersionId')->willReturn($lastVersionId);
        $this->stateMock->method('setVersionId')->willReturnSelf();
        $this->stateMock->expects($this->atLeastOnce())
            ->method('getMode')
            ->willReturn(StateInterface::MODE_ENABLED);
        $this->stateMock->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn(StateInterface::STATUS_IDLE);
        $this->stateMock->expects($this->exactly(2))
            ->method('setStatus')
            ->willReturnSelf();
        $this->stateMock->expects($this->exactly(2))
            ->method('save')
            ->willReturnSelf();
        $this->changelogMock
            ->expects($this->once())
            ->method('getVersion')
            ->willReturn($currentVersionId);

        $this->changelogMock->method('getList')
            ->willReturnMap(
                [
                    [$lastVersionId, 100001, $listIdBatchOne],
                    [100001, 200001, $listIdBatchTwo],
                    [200001, $currentVersionId, $listIdBatchThree],
                ]
            );

        $actionMock = $this->getMockForAbstractClass(ActionInterface::class);
        $actionMock->expects($this->once())
            ->method('execute')
            ->with($this->generateChangeLog(150, 1, 150))
            ->willReturnSelf();
        $this->actionFactoryMock->method('get')->willReturn($actionMock);
        $this->loadView();
        $this->model->update();
    }

    /**
     * Generate change log
     *
     * @param int $count
     * @param int $idFrom
     * @param int $idTo
     * @return array
     */
    private function generateChangeLog(int $count, int $idFrom, int $idTo): array
    {
        $res = [];
        $i = 0;
        $id = $idFrom;
        while ($i < $count) {
            if ($id > $idTo) {
                $id = $idFrom;
            }
            $res[] = $id;
            $id++;
            $i++;
        }

        return $res;
    }

    /**
     * Test to Materialize view by IDs in changelog
     *
     */
    public function testUpdateWithException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test exception');

        $currentVersionId = 3;
        $lastVersionId = 1;
        $listId = [2, 3];

        $this->stateMock->expects($this->any())
            ->method('getViewId')
            ->willReturn(1);
        $this->stateMock->expects($this->once())
            ->method('getVersionId')
            ->willReturn($lastVersionId);
        $this->stateMock->expects($this->never())
            ->method('setVersionId');
        $this->stateMock->expects($this->atLeastOnce())
            ->method('getMode')
            ->willReturn(StateInterface::MODE_ENABLED);
        $this->stateMock->expects($this->exactly(2))
            ->method('getStatus')
            ->willReturn(StateInterface::STATUS_IDLE);
        $this->stateMock->expects($this->exactly(2))
            ->method('setStatus')
            ->willReturnSelf();
        $this->stateMock->expects($this->exactly(2))
            ->method('save')
            ->willReturnSelf();

        $this->changelogMock->expects(
            $this->once()
        )->method(
            'getVersion'
        )->willReturn(
            $currentVersionId
        );
        $this->changelogMock->expects(
            $this->once()
        )->method(
            'getList'
        )->with(
            $lastVersionId,
            $currentVersionId
        )->willReturn(
            $listId
        );

        $actionMock = $this->createPartialMock(ActionInterface::class, ['execute']);
        $actionMock->expects($this->once())->method('execute')->with($listId)->willReturnCallback(
            
                function () {
                    throw new \Exception('Test exception');
                }
            
        );
        $this->actionFactoryMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'Some\Class\Name'
        )->willReturn(
            $actionMock
        );

        $this->loadView();
        $this->model->update();
    }

    /**
     * Test to Suspend view updates and set version ID to changelog's end
     */
    public function testSuspend()
    {
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(StateInterface::MODE_ENABLED);
        $this->stateMock->expects($this->once())
            ->method('setVersionId')
            ->with(11)
            ->willReturnSelf();
        $this->stateMock->expects($this->once())
            ->method('setStatus')
            ->with(StateInterface::STATUS_SUSPENDED)
            ->willReturnSelf();
        $this->stateMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->changelogMock->expects($this->once())
            ->method('getVersion')
            ->willReturn(11);

        $this->loadView();
        $this->model->suspend();
    }

    /**
     * Suspend view updates and set version ID to changelog's end
     */
    public function testSuspendDisabled()
    {
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(StateInterface::MODE_DISABLED);
        $this->stateMock->expects($this->never())
            ->method('setVersionId');
        $this->stateMock->expects($this->never())
            ->method('setStatus');
        $this->stateMock->expects($this->never())
            ->method('save');

        $this->changelogMock->expects($this->never())
            ->method('getVersion');

        $this->loadView();
        $this->model->suspend();
    }

    /**
     * Test to Resume view updates
     */
    public function testResume()
    {
        $this->stateMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(StateInterface::STATUS_SUSPENDED);
        $this->stateMock->expects($this->once())
            ->method('setStatus')
            ->with(StateInterface::STATUS_IDLE)
            ->willReturnSelf();
        $this->stateMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->loadView();
        $this->model->resume();
    }

    /**
     * Test to Resume view updates
     *
     * @param string $status
     * @dataProvider dataProviderResumeNotSuspended
     */
    public function testResumeNotSuspended($status)
    {
        $this->stateMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($status);
        $this->stateMock->expects($this->never())
            ->method('setStatus');
        $this->stateMock->expects($this->never())
            ->method('save');

        $this->loadView();
        $this->model->resume();
    }

    /**
     * @return array
     */
    public function dataProviderResumeNotSuspended()
    {
        return [
            [StateInterface::STATUS_IDLE],
            [StateInterface::STATUS_WORKING],
        ];
    }

    /**
     * Test to Clear precessed changelog entries
     */
    public function testClearChangelog()
    {
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(StateInterface::MODE_ENABLED);
        $this->stateMock->expects($this->once())
            ->method('getVersionId')
            ->willReturn(11);
        $this->changelogMock->expects($this->once())
            ->method('clear')
            ->with(11)
            ->willReturn(true);
        $this->loadView();
        $this->model->clearChangelog();
    }

    /**
     * Test to Clear precessed changelog entries
     */
    public function testClearChangelogDisabled()
    {
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn(StateInterface::MODE_DISABLED);
        $this->stateMock->expects($this->never())
            ->method('getVersionId');
        $this->changelogMock->expects($this->never())
            ->method('clear');
        $this->loadView();
        $this->model->clearChangelog();
    }

    /**
     * Test to Return related state object
     */
    public function testSetState()
    {
        $this->model->setState($this->stateMock);
        $this->assertEquals($this->stateMock, $this->model->getState());
    }

    /**
     * Test to Check whether view is enabled
     *
     * @param string $mode
     * @param bool $result
     * @dataProvider dataProviderIsEnabled
     */
    public function testIsEnabled($mode, $result)
    {
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn($mode);
        $this->assertEquals($result, $this->model->isEnabled());
    }

    /**
     * @return array
     */
    public function dataProviderIsEnabled()
    {
        return [
            [StateInterface::MODE_ENABLED, true],
            [StateInterface::MODE_DISABLED, false],
        ];
    }

    /**
     * Test to Check whether view is idle
     *
     * @param string $status
     * @param bool $result
     * @dataProvider dataProviderIsIdle
     */
    public function testIsIdle($status, $result)
    {
        $this->stateMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($status);
        $this->assertEquals($result, $this->model->isIdle());
    }

    /**
     * @return array
     */
    public function dataProviderIsIdle()
    {
        return [
            [StateInterface::STATUS_IDLE, true],
            [StateInterface::STATUS_WORKING, false],
            [StateInterface::STATUS_SUSPENDED, false],
        ];
    }

    /**
     * Test to Check whether view is working
     *
     * @param string $status
     * @param bool $result
     * @dataProvider dataProviderIsWorking
     */
    public function testIsWorking($status, $result)
    {
        $this->stateMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($status);
        $this->assertEquals($result, $this->model->isWorking());
    }

    /**
     * @return array
     */
    public function dataProviderIsWorking()
    {
        return [
            [StateInterface::STATUS_IDLE, false],
            [StateInterface::STATUS_WORKING, true],
            [StateInterface::STATUS_SUSPENDED, false],
        ];
    }

    /**
     * Test to Check whether view is suspended
     *
     * @param string $status
     * @param bool $result
     * @dataProvider dataProviderIsSuspended
     */
    public function testIsSuspended($status, $result)
    {
        $this->stateMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($status);
        $this->assertEquals($result, $this->model->isSuspended());
    }

    /**
     * @return array
     */
    public function dataProviderIsSuspended()
    {
        return [
            [StateInterface::STATUS_IDLE, false],
            [StateInterface::STATUS_WORKING, false],
            [StateInterface::STATUS_SUSPENDED, true],
        ];
    }

    /**
     * Test to Return view updated datetime
     */
    public function testGetUpdated()
    {
        $this->stateMock->expects($this->once())
            ->method('getUpdated')
            ->willReturn('some datetime');
        $this->assertEquals('some datetime', $this->model->getUpdated());
    }

    /**
     * Fill view data from config
     */
    protected function loadView()
    {
        $viewId = 'view_test';
        $this->configMock->expects(
            $this->once()
        )->method(
            'getView'
        )->with(
            $viewId
        )->willReturn(
            $this->getViewData()
        );
        $this->model->load($viewId);
    }

    /**
     * @return array
     */
    protected function getViewData()
    {
        return [
            'view_id' => 'view_test',
            'action_class' => 'Some\Class\Name',
            'group' => 'some_group',
            'subscriptions' => ['some_entity' => ['name' => 'some_entity', 'column' => 'entity_id']]
        ];
    }
}
