<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

class CustomerAuthenticatedEventObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Persistent\Observer\CustomerAuthenticatedEventObserver
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $observerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    protected function setUp(): void
    {
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $this->quoteManagerMock = $this->createMock(\Magento\Persistent\Model\QuoteManager::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\RequestInterface::class);
        $this->model = new \Magento\Persistent\Observer\CustomerAuthenticatedEventObserver(
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
            ->willReturnSelf();
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setCustomerGroupId')
            ->with(null)
            ->willReturnSelf();
        $this->requestMock
            ->expects($this->once())
            ->method('getParam')
            ->with('context')
            ->willReturn('not_checkout');
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
            ->willReturnSelf();
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setCustomerGroupId')
            ->with(null)
            ->willReturnSelf();
        $this->requestMock
            ->expects($this->once())
            ->method('getParam')
            ->with('context')
            ->willReturn('checkout');
        $this->quoteManagerMock->expects($this->never())->method('expire');
        $this->quoteManagerMock->expects($this->once())->method('setGuest');
        $this->model->execute($this->observerMock);
    }
}
