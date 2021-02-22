<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Event\InvokerInterface;
use Magento\Framework\Event\ConfigInterface;
use Magento\Framework\Event\Manager as EventManager;

/**
 * Class ManagerTest
 *
 * @package Magento\Framework\Event
 */
class ManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $invokerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $event;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $wrapperFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $observer;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventConfigMock;

    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $eventManager;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->invokerMock = $this->getMockForAbstractClass(InvokerInterface::class);
        $this->eventConfigMock = $this->getMockForAbstractClass(ConfigInterface::class);

        $this->eventManager = $this->objectManagerHelper->getObject(
            EventManager::class,
            [
                'invoker' => $this->invokerMock,
                'eventConfig' => $this->eventConfigMock
            ]
        );
    }

    public function testDispatch()
    {
        $this->eventConfigMock->expects($this->once())
            ->method('getObservers')
            ->with('some_eventname')
            ->willReturn(['observer' => ['instance' => 'class', 'method' => 'method', 'name' => 'observer']]);
        $this->eventManager->dispatch('some_eventName', ['123']);
    }

    public function testDispatchWithEmptyEventObservers()
    {
        $this->eventConfigMock->expects($this->once())
            ->method('getObservers')
            ->with('some_event')
            ->willReturn([]);
        $this->invokerMock->expects($this->never())->method('dispatch');
        $this->eventManager->dispatch('some_event');
    }
}
