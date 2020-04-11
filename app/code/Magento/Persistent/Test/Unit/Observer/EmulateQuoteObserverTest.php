<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

use PHPUnit\Framework\TestCase;
use Magento\Persistent\Observer\EmulateQuoteObserver;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Persistent\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event;
use Magento\Framework\App\Request\Http;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Quote\Model\Quote;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmulateQuoteObserverTest extends TestCase
{
    /**
     * @var EmulateQuoteObserver
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $customerRepository;

    /**
     * @var MockObject
     */
    protected $customerSessionMock;

    /**
     * @var MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var MockObject
     */
    protected $helperMock;

    /**
     * @var MockObject
     */
    protected $observerMock;

    /**
     * @var MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var MockObject
     */
    protected $eventMock;

    /**
     * @var MockObject
     */
    protected $requestMock;

    /**
     * @var MockObject
     */
    protected $customerMock;

    /**
     * @var MockObject
     */
    protected $sessionMock;

    protected function setUp(): void
    {
        $eventMethods = ['getRequest', 'dispatch', '__wakeUp'];
        $this->customerRepository = $this->getMockForAbstractClass(
            CustomerRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->sessionHelperMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->helperMock = $this->createMock(Data::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->checkoutSessionMock = $this->createPartialMock(
            \Magento\Checkout\Model\Session::class,
            ['isLoggedIn', 'setCustomerData', 'hasQuote', 'getQuote']
        );
        $this->eventMock = $this->createPartialMock(Event::class, $eventMethods);
        $this->requestMock = $this->createMock(Http::class);
        $this->customerMock = $this->createMock(CustomerInterface::class);
        $this->sessionMock =
            $this->createPartialMock(\Magento\Persistent\Model\Session::class, ['getCustomerId', '__wakeUp']);
        $this->model = new EmulateQuoteObserver(
            $this->sessionHelperMock,
            $this->helperMock,
            $this->checkoutSessionMock,
            $this->customerSessionMock,
            $this->customerRepository
        );
    }

    public function testExecuteWhenCannotProcess()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(false));
        $this->sessionHelperMock->expects($this->never())->method('isPersistent');
        $this->observerMock->expects($this->never())->method('getEvent');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenSessionIsNotPersistent()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(false));
        $this->checkoutSessionMock->expects($this->never())->method('isLoggedIn');
        $this->observerMock->expects($this->never())->method('getEvent');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenCustomerLoggedIn()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(true));
        $this->observerMock->expects($this->never())->method('getEvent');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenActionIsStop()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($this->eventMock));
        $this->eventMock->expects($this->once())->method('getRequest')->will($this->returnValue($this->requestMock));
        $this->requestMock
            ->expects($this->once())
            ->method('getFullActionName')
            ->will($this->returnValue('persistent_index_saveMethod'));
        $this->helperMock->expects($this->never())->method('isShoppingCartPersist');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenShoppingCartIsPersistent()
    {
        $customerId = 1;
        $quoteMock = $this->createMock(Quote::class);
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($this->eventMock));
        $this->eventMock->expects($this->once())->method('getRequest')->will($this->returnValue($this->requestMock));
        $this->requestMock
            ->expects($this->once())
            ->method('getFullActionName')
            ->will($this->returnValue('method_name'));
        $this->helperMock
            ->expects($this->once())
            ->method('isShoppingCartPersist')
            ->will($this->returnValue(true));
        $this->sessionHelperMock
            ->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($this->sessionMock));
        $this->sessionMock->expects($this->once())->method('getCustomerId')->will($this->returnValue($customerId));
        $this->customerRepository
            ->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->will($this->returnValue($this->customerMock));
        $this->checkoutSessionMock->expects($this->once())->method('setCustomerData')->with($this->customerMock);
        $this->checkoutSessionMock->expects($this->once())->method('hasQuote')->will($this->returnValue(false));
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->will($this->returnValue($quoteMock));
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenShoppingCartIsNotPersistent()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($this->eventMock));
        $this->eventMock->expects($this->once())->method('getRequest')->will($this->returnValue($this->requestMock));
        $this->requestMock
            ->expects($this->once())
            ->method('getFullActionName')
            ->will($this->returnValue('method_name'));
        $this->helperMock
            ->expects($this->once())
            ->method('isShoppingCartPersist')
            ->will($this->returnValue(false));
        $this->checkoutSessionMock->expects($this->never())->method('setCustomerData');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenShoppingCartIsPersistentAndQuoteExist()
    {
        $customerId = 1;
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($this->eventMock));
        $this->eventMock->expects($this->once())->method('getRequest')->will($this->returnValue($this->requestMock));
        $this->requestMock
            ->expects($this->once())
            ->method('getFullActionName')
            ->will($this->returnValue('method_name'));
        $this->helperMock
            ->expects($this->once())
            ->method('isShoppingCartPersist')
            ->will($this->returnValue(true));
        $this->sessionHelperMock
            ->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($this->sessionMock));
        $this->sessionMock->expects($this->once())->method('getCustomerId')->will($this->returnValue($customerId));
        $this->customerRepository
            ->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->will($this->returnValue($this->customerMock));
        $this->checkoutSessionMock->expects($this->once())->method('hasQuote')->will($this->returnValue(true));
        $this->checkoutSessionMock->expects($this->once())->method('setCustomerData')->with($this->customerMock);
        $this->model->execute($this->observerMock);
    }
}
