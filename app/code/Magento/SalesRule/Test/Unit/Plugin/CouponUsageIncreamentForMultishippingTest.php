<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Plugin;

use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\SalesRule\Model\Coupon\Quote\UpdateCouponUsages;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\PlaceOrderDefault;
use Magento\Sales\Model\Order;
use Magento\SalesRule\Plugin\CouponUsagesIncrementMultishipping;

class CouponUsageIncreamentForMultishippingTest extends TestCase
{
    /**
     * @var PlaceOrderDefault|MockObject
     */
    private $subjectMock;

    /**
     * @var UpdateCouponUsages|MockObject
     */
    private $updateCouponUsagesMock;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    private $cartRepositoryInterfaceMock;

    /**
     * @var Order[]|MockObject
     */
    private $orderMock;

    /**
     * @var CouponUsagesIncrementMultishipping
     */
    private $plugin;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $this->subjectMock = $this->getMockBuilder(PlaceOrderDefault::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->updateCouponUsagesMock = $this->getMockBuilder(UpdateCouponUsages::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['execute'])
            ->getMock();
        $this->cartRepositoryInterfaceMock = $this->getMockBuilder(CartRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock = $this->getMockBuilder(Order::class)
            ->onlyMethods(['getQuoteId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->plugin = new CouponUsagesIncrementMultishipping(
            $this->updateCouponUsagesMock,
            $this->cartRepositoryInterfaceMock
        );
    }
    /**
     * Testing Increments number of coupon usages before placing order
     */
    public function testAroundPlace()
    {
        $couponCode = 'coupon code';
        $proceed = function ($orderMock) {
            return $orderMock;
        };
        /** @var Quote|MockObject $quote */
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCouponCode'])
            ->onlyMethods(['dataHasChangedFor'])
            ->getMock();
        $this->orderMock->expects($this->once())->method('getQuoteId')
            ->willReturn(1);

        $this->cartRepositoryInterfaceMock->expects($this->once())->method('get')->with(1)->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getCouponCode')->willReturn($couponCode);
        $quoteMock->expects($this->any())->method('dataHasChangedFor')->with($couponCode)->willReturn(true);
        $this->updateCouponUsagesMock
            ->expects($this->once())
            ->method('execute');
        $this->assertSame(
            [$this->orderMock],
            $this->plugin->aroundPlace($this->subjectMock, $proceed, [$this->orderMock])
        );
    }
}
