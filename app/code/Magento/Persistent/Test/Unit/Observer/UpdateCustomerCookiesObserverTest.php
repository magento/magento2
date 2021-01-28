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
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerRepository;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $observerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerMock;

    protected function setUp(): void
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
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->willReturn(false);
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
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventManagerMock);
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getCustomerCookies')
            ->willReturn($cookieMock);
        $this->sessionHelperMock
            ->expects($this->once())
            ->method('getSession')
            ->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->customerRepository
            ->expects($this->once())
            ->method('getById')
            ->willReturn($this->customerMock);
        $this->customerMock->expects($this->once())->method('getId')->willReturn($customerId);
        $this->customerMock->expects($this->once())->method('getGroupId')->willReturn($customerGroupId);
        $cookieMock->expects($this->once())->method('setCustomerId')->with($customerId)->willReturnSelf();
        $cookieMock
            ->expects($this->once())
            ->method('setCustomerGroupId')
            ->with($customerGroupId)
            ->willReturnSelf();
        $this->model->execute($this->observerMock);
    }
}
