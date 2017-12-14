<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

/**
 * Class UpdateCustomerCookiesTest
 */
class UpdateCustomerCookiesObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Persistent\Observer\UpdateCustomerCookiesObserver
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    protected function setUp()
    {
        $eventMethods = ['getCustomerCookies', '__wakeUp'];
        $sessionMethods = ['getId', 'getGroupId', 'getCustomerId', '__wakeUp'];
        $this->sessionHelperMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->customerRepository = $this->getMockForAbstractClass(
            \Magento\Customer\Api\CustomerRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $this->eventManagerMock = $this->createPartialMock(\Magento\Framework\Event::class, $eventMethods);
        $this->sessionMock = $this->createPartialMock(\Magento\Persistent\Model\Session::class, $sessionMethods);
        $this->customerMock = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\CustomerInterface::class,
            [],
            '',
            false
        );
        $this->model = new \Magento\Persistent\Observer\UpdateCustomerCookiesObserver(
            $this->sessionHelperMock,
            $this->customerRepository
        );
    }

    public function testExecuteWhenSessionNotPersistent()
    {
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(false));
        $this->observerMock->expects($this->never())->method('getEvent');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenCustomerCookieExist()
    {
        $customerId = 1;
        $customerGroupId = 2;
        $cookieMock = $this->createPartialMock(
            \Magento\Framework\DataObject::class,
            ['setCustomerId', 'setCustomerGroupId', '__wakeUp']
        );
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->eventManagerMock));
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getCustomerCookies')
            ->will($this->returnValue($cookieMock));
        $this->sessionHelperMock
            ->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($this->sessionMock));
        $this->sessionMock->expects($this->once())->method('getCustomerId')->will($this->returnValue($customerId));
        $this->customerRepository
            ->expects($this->once())
            ->method('getById')
            ->will($this->returnValue($this->customerMock));
        $this->customerMock->expects($this->once())->method('getId')->will($this->returnValue($customerId));
        $this->customerMock->expects($this->once())->method('getGroupId')->will($this->returnValue($customerGroupId));
        $cookieMock->expects($this->once())->method('setCustomerId')->with($customerId)->will($this->returnSelf());
        $cookieMock
            ->expects($this->once())
            ->method('setCustomerGroupId')
            ->with($customerGroupId)
            ->will($this->returnSelf());
        $this->model->execute($this->observerMock);
    }
}
