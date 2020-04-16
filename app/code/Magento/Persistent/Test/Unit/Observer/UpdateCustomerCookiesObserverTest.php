<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Persistent\Helper\Session;
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
        $eventMethods = ['getCustomerCookies', '__wakeUp'];
        $sessionMethods = ['getId', 'getGroupId', 'getCustomerId', '__wakeUp'];
        $this->sessionHelperMock = $this->createMock(Session::class);
        $this->customerRepository = $this->getMockForAbstractClass(
            CustomerRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->observerMock = $this->createMock(Observer::class);
        $this->eventManagerMock = $this->createPartialMock(Event::class, $eventMethods);
        $this->sessionMock = $this->createPartialMock(\Magento\Persistent\Model\Session::class, $sessionMethods);
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
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(false));
        $this->observerMock->expects($this->never())->method('getEvent');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenCustomerCookieExist()
    {
        $customerId = 1;
        $customerGroupId = 2;
        $cookieMock = $this->createPartialMock(
            DataObject::class,
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
