<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $invokerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $wrapperFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $observer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
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

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->invokerMock = $this->getMock(InvokerInterface::class);
        $this->eventConfigMock = $this->getMock(ConfigInterface::class);

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
