<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Event\Test\Unit;

use Magento\Framework\Event\ConfigInterface;
use Magento\Framework\Event\InvokerInterface;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $invokerMock;

    /**
     * @var MockObject
     */
    protected $eventFactory;

    /**
     * @var MockObject
     */
    protected $event;

    /**
     * @var MockObject
     */
    protected $wrapperFactory;

    /**
     * @var MockObject
     */
    protected $observer;

    /**
     * @var MockObject
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
