<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Observer\SynchronizePersistentInfoObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SynchronizePersistentInfoObserverTest extends TestCase
{
    /**
     * @var SynchronizePersistentInfoObserver
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $helperMock;

    /**
     * @var MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var MockObject
     */
    protected $customerSessionMock;

    /**
     * @var MockObject
     */
    protected $observerMock;

    /**
     * @var MockObject
     */
    protected $eventManagerMock;

    /**
     * @var MockObject
     */
    protected $sessionMock;

    /**
     * @var MockObject
     */
    protected $requestMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(Http::class);
        $this->helperMock = $this->createMock(Data::class);
        $this->sessionHelperMock = $this->createMock(Session::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->eventManagerMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getRequest'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->createMock(\Magento\Persistent\Model\Session::class);
        $this->model = new SynchronizePersistentInfoObserver(
            $this->helperMock,
            $this->sessionHelperMock,
            $this->customerSessionMock
        );
    }

    public function testSynchronizePersistentInfoWhenPersistentDataNotEnabled()
    {
        $this->helperMock->expects($this->once())->method('isEnabled')->willReturn(false);
        $this->sessionHelperMock->expects($this->never())->method('getSession');
        $this->model->execute($this->observerMock);
    }

    public function testSynchronizePersistentInfoWhenPersistentDataIsEnabled()
    {
        $this->helperMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->sessionHelperMock
            ->expects($this->once())
            ->method('getSession')
            ->willReturn($this->sessionMock);
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventManagerMock);
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->requestMock
            ->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('customer_account_logout');
        $this->sessionMock->expects($this->once())->method('save');
        $this->model->execute($this->observerMock);
    }
}
