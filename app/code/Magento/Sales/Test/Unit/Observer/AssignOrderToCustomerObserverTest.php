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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AssignOrderToCustomerObserverTest extends TestCase
{
    /** @var AssignOrderToCustomerObserver */
    protected $sut;

    /** @var OrderRepositoryInterface|MockObject */
    protected $orderRepositoryMock;

    /** @var CustomerAssignment|MockObject */
    protected $assignmentMock;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->assignmentMock =  $this->getMockBuilder(CustomerAssignment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->sut = new AssignOrderToCustomerObserver($this->orderRepositoryMock, $this->assignmentMock);
    }

    /**
     * Test assigning order to customer after issuing guest order
     *
     * @dataProvider getCustomerIds
     * @param null|int $orderCustomerId
     * @param null|int $customerId
     * @return void
     */
    public function testAssignOrderToCustomerAfterGuestOrder($orderCustomerId, $customerId)
    {
        $orderId = 1;
        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->createMock(Observer::class);
        /** @var Event|MockObject $eventMock */
        $eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->getMock();
        /** @var CustomerInterface|MockObject $customerMock */
        $customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        /** @var OrderInterface|MockObject $orderMock */
        $orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->any())->method('getData')
            ->willReturnMap(
                [
                    ['delegate_data', null, ['__sales_assign_order_id' => $orderId]],
                    ['customer_data_object', null, $customerMock]
                ]
            );
        $orderMock->expects($this->once())->method('getCustomerId')->willReturn($orderCustomerId);
        $this->orderRepositoryMock->expects($this->once())->method('get')->with($orderId)
            ->willReturn($orderMock);

        if (!$orderCustomerId) {
            $customerMock->expects($this->once())->method('getId')->willReturn($customerId);
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
    public static function getCustomerIds()
    {
        return [
            [null, 1],
            [1, 1],
        ];
    }
}
