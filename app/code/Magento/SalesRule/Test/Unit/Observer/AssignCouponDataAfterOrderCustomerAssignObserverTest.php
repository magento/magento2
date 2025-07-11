<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\SalesRule\Model\Coupon\UpdateCouponUsages;
use Magento\SalesRule\Observer\AssignCouponDataAfterOrderCustomerAssignObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\SalesRule\Observer\AssignCouponDataAfterOrderCustomerAssignObserver
 */
class AssignCouponDataAfterOrderCustomerAssignObserverTest extends TestCase
{
    /*
     * Stub event key order
     */
    private const STUB_EVENT_KEY_ORDER = 'order';

    /*
     * Stub customer ID
     */
    private const STUB_CUSTOMER_ID = 1;

    /**
     * Testable Object
     *
     * @var AssignCouponDataAfterOrderCustomerAssignObserver
     */
    private $observer;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var Event|MockObject
     */
    private $eventMock;

    /**
     * @var OrderInterface|MockObject
     */
    private $orderMock;

    /**
     * @var UpdateCouponUsages|MockObject
     */
    private $updateCouponUsagesMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->observerMock = $this->createMock(Observer::class);

        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->getMock();

        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->updateCouponUsagesMock = $this->getMockBuilder(UpdateCouponUsages::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['execute'])
            ->getMock();

        $this->observer = $this->objectManager->getObject(
            AssignCouponDataAfterOrderCustomerAssignObserver::class,
            [
                'updateCouponUsages' => $this->updateCouponUsagesMock
            ]
        );
    }

    /**
     * Test for execute(), covers test case for assign coupon data after order customer
     */
    public function testExecuteAssignCouponData(): void
    {
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock
            ->expects($this->once())
            ->method('getData')
            ->with(self::STUB_EVENT_KEY_ORDER)
            ->willReturn($this->orderMock);

        $this->orderMock
            ->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(self::STUB_CUSTOMER_ID);

        $this->updateCouponUsagesMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->orderMock, true);

        $this->observer->execute($this->observerMock);
    }

    /**
     * Test for execute(), covers test case for assign coupon data after order customer with empty customer ID
     */
    public function testExecuteAssignCouponDataWithEmptyCustomerId(): void
    {
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock
            ->expects($this->once())
            ->method('getData')
            ->with(self::STUB_EVENT_KEY_ORDER)
            ->willReturn($this->orderMock);

        $this->orderMock
            ->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(null);

        $this->updateCouponUsagesMock
            ->expects($this->never())
            ->method('execute');

        $this->observer->execute($this->observerMock);
    }
}
