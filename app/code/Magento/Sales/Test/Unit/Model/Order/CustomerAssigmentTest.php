<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\CustomerAssignment;

/**
 * Test for Magento\Sales\Model\Order\CustomerAssignment class.
 */
class CustomerAssigmentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CustomerAssignment
     */
    private $customerAssignment;

    /**
     * @var OrderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderMock;

    /**
     * @var CustomerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customerMock;

    /**
     * @var OrderRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventManagerMock;

    /**
     * Tests 'execute' method.
     *
     * @dataProvider executeDataProvider
     * @param array $data
     */
    public function testExecute(array $data): void
    {
        $this->configureOrderMock($data);
        $this->configureCustomerMock($data);
        $this->orderRepositoryMock->expects($this->once())->method('save')->with($this->orderMock);
        $this->eventManagerMock->expects($this->once())->method('dispatch')->with(
            'sales_order_customer_assign_after',
            [
                'order' => $this->orderMock,
                'customer' => $this->customerMock
            ]
        );

        $this->customerAssignment->execute($this->orderMock, $this->customerMock);
    }

    /**
     *
     * Data provider for testExecute.
     * @return array
     */
    public function executeDataProvider(): array
    {
        return [
            [
                [
                    'customerId' => 1,
                    'customerIsGuest' => false,
                    'customerEmail' => 'customerEmail',
                    'customerFirstname' => 'customerFirstname',
                    'customerLastname' => 'customerLastname',
                    'customerMiddlename' => 'customerMiddlename',
                    'customerPrefix' => 'customerPrefix',
                    'customerSuffix' => 'customerSuffix',
                    'customerGroupId' => 'customerGroupId',
                ],
            ],
        ];
    }

    /**
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->orderMock = $this->createMock(OrderInterface::class);
        $this->customerMock = $this->createMock(CustomerInterface::class);
        $this->orderRepositoryMock = $this->createMock(OrderRepositoryInterface::class);
        $this->eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->customerAssignment = $objectManager->getObject(
            CustomerAssignment::class,
            [
                'eventManager' => $this->eventManagerMock,
                'orderRepository' => $this->orderRepositoryMock
            ]
        );
    }

    /**
     *  Set up order mock.
     *
     * @param array $data
     */
    private function configureOrderMock(array $data): void
    {
        $this->orderMock->expects($this->once())->method('setCustomerId')->with($data['customerId'])
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())->method('setCustomerIsGuest')->with($data['customerIsGuest'])
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())->method('setCustomerEmail')->with($data['customerEmail'])
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())->method('setCustomerFirstname')->with($data['customerFirstname'])
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())->method('setCustomerLastname')->with($data['customerLastname'])
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())->method('setCustomerMiddlename')->with($data['customerMiddlename'])
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())->method('setCustomerPrefix')->with($data['customerPrefix'])
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())->method('setCustomerSuffix')->with($data['customerSuffix'])
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->once())->method('setCustomerGroupId')->with($data['customerGroupId'])
            ->willReturn($this->orderMock);
    }

    /**
     * Set up customer mock.
     *
     * @param array $data
     */
    private function configureCustomerMock(array $data): void
    {
        $this->customerMock->expects($this->once())->method('getId')->willReturn($data['customerId']);
        $this->customerMock->expects($this->once())->method('getEmail')->willReturn($data['customerEmail']);
        $this->customerMock->expects($this->once())->method('getFirstname')->willReturn($data['customerFirstname']);
        $this->customerMock->expects($this->once())->method('getLastname')->willReturn($data['customerLastname']);
        $this->customerMock->expects($this->once())->method('getMiddlename')->willReturn($data['customerMiddlename']);
        $this->customerMock->expects($this->once())->method('getPrefix')->willReturn($data['customerPrefix']);
        $this->customerMock->expects($this->once())->method('getSuffix')->willReturn($data['customerSuffix']);
        $this->customerMock->expects($this->once())->method('getGroupId')->willReturn($data['customerGroupId']);
    }
}
