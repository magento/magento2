<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\Test\Unit;

use Magento\Framework\Mview\ActionFactory;
use Magento\Framework\Mview\ConfigInterface;
use Magento\Framework\Mview\View;
use Magento\Framework\Mview\View\Changelog;
use Magento\Framework\Mview\View\ChangelogBatchWalkerFactory;
use Magento\Framework\Mview\View\ChangelogBatchWalkerInterface;
use Magento\Framework\Mview\View\StateInterface;
use Magento\Framework\Mview\View\Subscription;
use Magento\Framework\Mview\View\SubscriptionFactory;
use Magento\Indexer\Model\Mview\View\State;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** test Mview functionality
 */
class ViewSubscribeTest extends TestCase
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
     * @var MockObject|ChangelogBatchWalkerInterface
     */
    private $iteratorMock;

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
        $this->iteratorMock = $this->getMockBuilder(ChangelogBatchWalkerInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['walk'])
            ->getMockForAbstractClass();
        $changeLogBatchWalkerFactory = $this->getMockBuilder(ChangelogBatchWalkerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMockForAbstractClass();
        $changeLogBatchWalkerFactory->method('create')->willReturn($this->iteratorMock);
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
            $this->subscriptionFactoryMock,
            [],
            [],
            $changeLogBatchWalkerFactory
        );
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
            ->with(StateInterface::MODE_ENABLED)->willReturnSelf();
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

    public function testSubscribeWithException()
    {
        $this->expectException('Exception');
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
            ->with(StateInterface::MODE_DISABLED)->willReturnSelf();
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

    public function testUnsubscribeWithException()
    {
        $this->expectException('Exception');
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
     * Fill view data from config
     */
    protected function loadView()
    {
        $viewId = 'view_test';
        $this->changelogMock->expects($this->any())
            ->method('getViewId')
            ->willReturn($viewId);
        $this->configMock->expects(
            $this->any()
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
            'subscriptions' => ['some_entity' => ['name' => 'some_entity', 'column' => 'entity_id']],
            'walker' => ChangelogBatchWalkerInterface::class
        ];
    }
}
