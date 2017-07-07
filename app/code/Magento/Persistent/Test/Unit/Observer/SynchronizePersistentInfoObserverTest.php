<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

class SynchronizePersistentInfoObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Observer\SynchronizePersistentInfoObserver
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->helperMock = $this->getMock(\Magento\Persistent\Helper\Data::class, [], [], '', false);
        $this->sessionHelperMock = $this->getMock(\Magento\Persistent\Helper\Session::class, [], [], '', false);
        $this->customerSessionMock = $this->getMock(\Magento\Customer\Model\Session::class, [], [], '', false);
        $this->observerMock = $this->getMock(\Magento\Framework\Event\Observer::class, [], [], '', false);
        $eventMethods = ['getRequest', '__wakeUp'];
        $this->eventManagerMock = $this->getMock(\Magento\Framework\Event::class, $eventMethods, [], '', false);
        $this->sessionMock = $this->getMock(\Magento\Persistent\Model\Session::class, [], [], '', false);
        $this->model = new \Magento\Persistent\Observer\SynchronizePersistentInfoObserver(
            $this->helperMock,
            $this->sessionHelperMock,
            $this->customerSessionMock
        );
    }

    public function testSynchronizePersistentInfoWhenPersistentDataNotEnabled()
    {
        $this->helperMock->expects($this->once())->method('isEnabled')->will($this->returnValue(false));
        $this->sessionHelperMock->expects($this->never())->method('getSession');
        $this->model->execute($this->observerMock);
    }

    public function testSynchronizePersistentInfoWhenPersistentDataIsEnabled()
    {
        $this->helperMock->expects($this->once())->method('isEnabled')->will($this->returnValue(true));
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->sessionHelperMock
            ->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($this->sessionMock));
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->eventManagerMock));
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->requestMock
            ->expects($this->once())
            ->method('getFullActionName')
            ->will($this->returnValue('customer_account_logout'));
        $this->sessionMock->expects($this->once())->method('save');
        $this->model->execute($this->observerMock);
    }
}
