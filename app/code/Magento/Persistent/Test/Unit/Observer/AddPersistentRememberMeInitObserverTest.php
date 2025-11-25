<?php
/**
 * Copyright 2024 Adobe.
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\ProcessorInterface;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Observer\AddPersistentRememberMeInitObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddPersistentRememberMeInitObserverTest extends TestCase
{

    /**
     * @var Data|MockObject
     */
    private $persistentData;

    /**
     * @var Session|MockObject
     */
    private $customerSession;

    /**
     * @var Layout|MockObject
     */
    private $layout;

    /**
     * @var Observer|MockObject
     */
    private $eventObserver;

    /**
     * @var AddPersistentRememberMeInitObserver
     */
    private $observer;

    protected function setUp(): void
    {
        $this->persistentData = $this->createMock(Data::class);
        $this->customerSession = $this->createMock(Session::class);
        $this->layout = $this->createMock(Layout::class);
        $this->eventObserver = $this->createMock(Observer::class);

        $this->observer = new AddPersistentRememberMeInitObserver(
            $this->persistentData,
            $this->customerSession
        );
    }

    public function testExecuteAddsHandle()
    {
        $this->customerSession->method('isLoggedIn')->willReturn(false);
        $this->persistentData->method('isEnabled')->willReturn(true);
        $this->persistentData->method('isRememberMeEnabled')->willReturn(true);
        $processor = $this->createMock(ProcessorInterface::class);
        $processor->expects($this->once())
            ->method('addHandle')
            ->with('remember_me');
        $data = $this->createMock(DataObject::class);

        $data->method('getData')
            ->with('layout')
            ->willReturn($this->layout);

        $this->eventObserver
            ->method('getEvent')
            ->willReturn($data);
        $this->layout->expects($this->once())
            ->method('getUpdate')
            ->willReturn($processor);

        $this->observer->execute($this->eventObserver);
    }

    public function testExecuteDoesNotAddHandleWhenLoggedIn()
    {
        $this->customerSession->method('isLoggedIn')->willReturn(true);

        $this->layout->expects($this->never())
            ->method('getUpdate');

        $this->observer->execute($this->eventObserver);
    }

    public function testExecuteDoesNotAddHandleWhenPersistentDisabled()
    {
        $this->customerSession->method('isLoggedIn')->willReturn(false);
        $this->persistentData->method('isEnabled')->willReturn(false);

        $this->layout->expects($this->never())
            ->method('getUpdate');

        $this->observer->execute($this->eventObserver);
    }

    public function testExecuteDoesNotAddHandleWhenRememberMeDisabled()
    {
        $this->customerSession->method('isLoggedIn')->willReturn(false);
        $this->persistentData->method('isEnabled')->willReturn(true);
        $this->persistentData->method('isRememberMeEnabled')->willReturn(false);

        $this->layout->expects($this->never())
            ->method('getUpdate');

        $this->observer->execute($this->eventObserver);
    }
}
