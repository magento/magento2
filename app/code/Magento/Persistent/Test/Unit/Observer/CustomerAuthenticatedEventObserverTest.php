<?php declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Persistent\Model\QuoteManager;
use Magento\Persistent\Observer\CustomerAuthenticatedEventObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerAuthenticatedEventObserverTest extends TestCase
{
    /**
     * @var CustomerAuthenticatedEventObserver
     */
    protected $model;

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
    protected $quoteManagerMock;

    /**
     * @var MockObject
     */
    protected $requestMock;

    protected function setUp(): void
    {
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->quoteManagerMock = $this->createMock(QuoteManager::class);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->model = new CustomerAuthenticatedEventObserver(
            $this->customerSessionMock,
            $this->requestMock,
            $this->quoteManagerMock
        );
    }

    public function testExecute()
    {
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setCustomerId')
            ->with(null)
            ->will($this->returnSelf());
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setCustomerGroupId')
            ->with(null)
            ->will($this->returnSelf());
        $this->requestMock
            ->expects($this->once())
            ->method('getParam')
            ->with('context')
            ->will($this->returnValue('not_checkout'));
        $this->quoteManagerMock->expects($this->once())->method('expire');
        $this->quoteManagerMock->expects($this->never())->method('setGuest');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteDuringCheckout()
    {
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setCustomerId')
            ->with(null)
            ->will($this->returnSelf());
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setCustomerGroupId')
            ->with(null)
            ->will($this->returnSelf());
        $this->requestMock
            ->expects($this->once())
            ->method('getParam')
            ->with('context')
            ->will($this->returnValue('checkout'));
        $this->quoteManagerMock->expects($this->never())->method('expire');
        $this->quoteManagerMock->expects($this->once())->method('setGuest');
        $this->model->execute($this->observerMock);
    }
}
