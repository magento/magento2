<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Observer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Observer\AssignOrderToCustomerObserver;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class AssignOrderToCustomerObserverTest
 */
class AssignOrderToCustomerObserverTest extends TestCase
{
    /** @var AssignOrderToCustomerObserver */
    protected $sut;

    /** @var OrderRepositoryInterface|PHPUnit_Framework_MockObject_MockObject */
    protected $orderRepositoryMock;

    /**
     * Set Up
     */
    protected function setUp()
    {
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sut = new AssignOrderToCustomerObserver($this->orderRepositoryMock);
    }

    /**
     * Test assigning order to customer after issuing guest order
     *
     * @dataProvider getCustomerIds
     * @param null|int $customerId
     * @return void
     */
    public function testAssignOrderToCustomerAfterGuestOrder($customerId)
    {
        $orderId = 1;
        /** @var Observer|PHPUnit_Framework_MockObject_MockObject $observerMock */
        $observerMock = $this->createMock(Observer::class);
        /** @var Event|PHPUnit_Framework_MockObject_MockObject $eventMock */
        $eventMock = $this->getMockBuilder(Event::class)->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();
        /** @var CustomerInterface|PHPUnit_Framework_MockObject_MockObject $customerMock */
        $customerMock = $this->createMock(CustomerInterface::class);
        /** @var OrderInterface|PHPUnit_Framework_MockObject_MockObject $orderMock */
        $orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->any())->method('getData')
            ->willReturnMap([
                ['delegate_data', null, ['__sales_assign_order_id' => $orderId]],
                ['customer_data_object', null, $customerMock]
            ]);
        $orderMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->orderRepositoryMock->expects($this->once())->method('get')->with($orderId)
            ->willReturn($orderMock);

        $orderMock->expects($this->once())->method('setCustomerId')->willReturn($orderMock);
        $orderMock->expects($this->once())->method('setCustomerIsGuest')->willReturn($orderMock);
        $orderMock->expects($this->once())->method('setCustomerEmail')->willReturn($orderMock);
        $orderMock->expects($this->once())->method('setCustomerFirstname')->willReturn($orderMock);
        $orderMock->expects($this->once())->method('setCustomerLastname')->willReturn($orderMock);
        $orderMock->expects($this->once())->method('setCustomerMiddlename')->willReturn($orderMock);
        $orderMock->expects($this->once())->method('setCustomerPrefix')->willReturn($orderMock);
        $orderMock->expects($this->once())->method('setCustomerSuffix')->willReturn($orderMock);
        $orderMock->expects($this->once())->method('setCustomerGroupId')->willReturn($orderMock);

        if (!$customerId) {
            $this->orderRepositoryMock->expects($this->once())->method('save')->with($orderMock);
            $this->sut->execute($observerMock);
            return ;
        }

        $this->orderRepositoryMock->expects($this->never())->method('save')->with($orderMock);
        $this->sut->execute($observerMock);
    }

    /**
     * Customer id assigned to order
     *
     * @return array
     */
    public function getCustomerIds()
    {
        return [[null, 1]];
    }
}
