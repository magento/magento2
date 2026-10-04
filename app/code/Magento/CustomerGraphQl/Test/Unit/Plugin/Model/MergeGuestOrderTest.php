<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Test\Unit\Plugin\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\CustomerGraphQl\Model\GetGuestOrdersByEmail;
use Magento\CustomerGraphQl\Plugin\Model\MergeGuestOrder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Model\Order\CustomerAssignment;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MergeGuestOrderTest extends TestCase
{
    /**
     * @var GetGuestOrdersByEmail|MockObject
     */
    private GetGuestOrdersByEmail $getGuestOrdersByEmail;

    /**
     * @var CustomerAssignment|MockObject
     */
    private CustomerAssignment $customerAssignment;

    /**
     * @var MergeGuestOrder
     */
    private MergeGuestOrder $mergeGuestOrder;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->customerAssignment = $this->createMock(CustomerAssignment::class);
        $this->getGuestOrdersByEmail = $this->createMock(GetGuestOrdersByEmail::class);
        $this->mergeGuestOrder = new MergeGuestOrder(
            $this->getGuestOrdersByEmail,
            $this->customerAssignment
        );
        parent::setUp();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testAfterCreateAccount(): void
    {
        $subject = $this->createMock(AccountManagement::class);
        $customer = $this->createMock(CustomerInterface::class);
        $result = $this->createMock(OrderSearchResultInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $result->expects($this->once())->method('getItems')->willReturn([$order]);

        $this->getGuestOrdersByEmail->expects($this->once())
            ->method('execute')
            ->with($customer)
            ->willReturn($result);
        $this->customerAssignment->expects($this->once())->method('execute')->with($order, $customer);

        $this->mergeGuestOrder->afterCreateAccount($subject, $customer);
    }
}
