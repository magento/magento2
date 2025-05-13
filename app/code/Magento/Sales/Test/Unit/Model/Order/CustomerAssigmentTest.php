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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\Sales\Model\Order\CustomerAssignment class.
 */
class CustomerAssigmentTest extends TestCase
{
    /**
     * @var CustomerAssignment
     */
    private $customerAssignment;

    /**
     * @var OrderInterface|MockObject
     */
    private $orderMock;

    /**
     * @var CustomerInterface|MockObject
     */
    private $customerMock;

    /**
     * @var OrderRepositoryInterface|MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var ManagerInterface|MockObject
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
    public static function executeDataProvider(): array
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
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->orderMock = $this->getMockForAbstractClass(OrderInterface::class);
        $this->customerMock = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->orderRepositoryMock = $this->getMockForAbstractClass(OrderRepositoryInterface::class);
        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
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
