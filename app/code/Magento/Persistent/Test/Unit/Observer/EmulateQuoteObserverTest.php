<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session as PersistentSessionHelper;
use Magento\Persistent\Model\Session as PersistentSessionModel;
use Magento\Persistent\Observer\EmulateQuoteObserver;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
        $this->customerRepository = $this->getMockForAbstractClass(
            CustomerRepositoryInterface::class,
            [],
            '',
            false
        );
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->sessionHelperMock = $this->createMock(PersistentSessionHelper::class);
        $this->helperMock = $this->createMock(Data::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->checkoutSessionMock = $this->getMockBuilder(CheckoutSession::class)
            ->addMethods(['isLoggedIn'])
            ->onlyMethods(['setCustomerData', 'hasQuote', 'getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getRequest'])
            ->onlyMethods(['dispatch'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->createMock(Http::class);
        $this->customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->sessionMock =
            $this->getMockBuilder(PersistentSessionModel::class)
                ->addMethods(['getCustomerId'])
                ->disableOriginalConstructor()
                ->getMock();
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
            ->willReturn(false);
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
            ->willReturn(true);
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->willReturn(false);
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
            ->willReturn(true);
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->observerMock->expects($this->never())->method('getEvent');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenActionIsStop()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->willReturn(true);
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->observerMock->expects($this->once())->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->requestMock
            ->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('persistent_index_saveMethod');
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
            ->willReturn(true);
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->observerMock->expects($this->once())->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->requestMock
            ->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('method_name');
        $this->helperMock
            ->expects($this->once())
            ->method('isShoppingCartPersist')
            ->willReturn(true);
        $this->sessionHelperMock
            ->expects($this->once())
            ->method('getSession')
            ->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->customerRepository
            ->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($this->customerMock);
        $this->checkoutSessionMock->expects($this->once())->method('setCustomerData')->with($this->customerMock);
        $this->checkoutSessionMock->expects($this->once())->method('hasQuote')->willReturn(false);
        $this->checkoutSessionMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenShoppingCartIsNotPersistent()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->willReturn(true);
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->observerMock->expects($this->once())->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->requestMock
            ->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('method_name');
        $this->helperMock
            ->expects($this->once())
            ->method('isShoppingCartPersist')
            ->willReturn(false);
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
            ->willReturn(true);
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->observerMock->expects($this->once())->method('getEvent')->willReturn($this->eventMock);
        $this->eventMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->requestMock
            ->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('method_name');
        $this->helperMock
            ->expects($this->once())
            ->method('isShoppingCartPersist')
            ->willReturn(true);
        $this->sessionHelperMock
            ->expects($this->once())
            ->method('getSession')
            ->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->customerRepository
            ->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($this->customerMock);
        $this->checkoutSessionMock->expects($this->once())->method('hasQuote')->willReturn(true);
        $this->checkoutSessionMock->expects($this->once())->method('setCustomerData')->with($this->customerMock);
        $this->model->execute($this->observerMock);
    }
}
