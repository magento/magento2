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
use Magento\Sales\Model\Order\CustomerAssignment;
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

    /** @var CustomerAssignment | PHPUnit_Framework_MockObject_MockObject */
    protected $assignmentMock;

    /**
     * Set Up
     */
    protected function setUp()
    {
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assignmentMock =  $this->getMockBuilder(CustomerAssignment::class)
        ->disableOriginalConstructor()
        ->getMock();

        $this->sut = new AssignOrderToCustomerObserver($this->orderRepositoryMock, $this->assignmentMock);
    }

    /**
     * Test assigning order to customer after issuing guest order
     *
     * @dataProvider getCustomerIds
     * @param null|int $customerId
     * @param null|int $customerOrderId
     * @return void
     */
    public function testAssignOrderToCustomerAfterGuestOrder($customerId, $customerOrderId)
    {
        $orderId = 1;
        /** @var Observer|PHPUnit_Framework_MockObject_MockObject $observerMock */
        $observerMock = $this->createMock(Observer::class);
        /** @var Event|PHPUnit_Framework_MockObject_MockObject $eventMock */
        $eventMock = $this->getMockBuilder(Event::class)->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();
        /** @var CustomerInterface|PHPUnit_Framework_MockObject_MockObject $customerMock */
        $customerMock = $this->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerMock->expects($this->any())
            ->method('getId')
            ->willReturn($customerId);
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
        $orderMock->expects($this->any())->method('getCustomerId')->willReturn($customerOrderId);
        $this->orderRepositoryMock->expects($this->once())->method('get')->with($orderId)
            ->willReturn($orderMock);

        if (!$customerOrderId && $customerId) {
            $orderMock->expects($this->once())->method('setCustomerId')->willReturn($orderMock);
            $orderMock->expects($this->once())->method('setCustomerIsGuest')->willReturn($orderMock);
            $orderMock->expects($this->once())->method('setCustomerEmail')->willReturn($orderMock);
            $orderMock->expects($this->once())->method('setCustomerFirstname')->willReturn($orderMock);
            $orderMock->expects($this->once())->method('setCustomerLastname')->willReturn($orderMock);
            $orderMock->expects($this->once())->method('setCustomerMiddlename')->willReturn($orderMock);
            $orderMock->expects($this->once())->method('setCustomerPrefix')->willReturn($orderMock);
            $orderMock->expects($this->once())->method('setCustomerSuffix')->willReturn($orderMock);
            $orderMock->expects($this->once())->method('setCustomerGroupId')->willReturn($orderMock);

            $this->assignmentMock->expects($this->once())->method('execute')->with($orderMock, $customerMock);
            $this->sut->execute($observerMock);

            return;
        }

        $this->assignmentMock->expects($this->never())->method('execute');
        $this->sut->execute($observerMock);
    }

    /**
     * Customer id assigned to order
     *
     * @return array
     */
    public function getCustomerIds(): array
    {
        return [
            [null, null],
            [1, null],
            [1, 1],
        ];
    }
}
