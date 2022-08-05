<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Model\Session as PersistentSessionModel;
use Magento\Persistent\Observer\UpdateCustomerCookiesObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class UpdateCustomerCookiesTest
 */
class UpdateCustomerCookiesObserverTest extends TestCase
{
    /**
     * @var UpdateCustomerCookiesObserver
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var MockObject
     */
    protected $customerRepository;

    /**
     * @var MockObject
     */
    protected $eventManagerMock;

    /**
     * @var MockObject
     */
    protected $observerMock;

    /**
     * @var MockObject
     */
    protected $sessionMock;

    /**
     * @var MockObject
     */
    protected $customerMock;

    protected function setUp(): void
    {
        $this->sessionHelperMock = $this->createMock(Session::class);
        $this->customerRepository = $this->getMockForAbstractClass(
            CustomerRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->observerMock = $this->createMock(Observer::class);
        $this->eventManagerMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getCustomerCookies'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->getMockBuilder(PersistentSessionModel::class)
            ->addMethods(['getGroupId', 'getCustomerId'])
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerMock = $this->getMockForAbstractClass(
            CustomerInterface::class,
            [],
            '',
            false
        );
        $this->model = new UpdateCustomerCookiesObserver(
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
        $cookieMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['setCustomerId', 'setCustomerGroupId'])
            ->disableOriginalConstructor()
            ->getMock();
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
            ->with($customerGroupId)->willReturnSelf();
        $this->model->execute($this->observerMock);
    }
}
