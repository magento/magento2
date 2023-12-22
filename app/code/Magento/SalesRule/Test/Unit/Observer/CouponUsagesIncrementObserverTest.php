<?php
/************************************************************************
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Model\Coupon\Quote\UpdateCouponUsages;
use Magento\SalesRule\Observer\CouponUsagesIncrementObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CouponUsagesIncrementObserverTest extends TestCase
{
    /**
     * @var CouponUsagesIncrementObserver
     */
    private $couponUsagesIncrementObserver;

    /**
     * @var MockObject&UpdateCouponUsages
     */
    private $updateCouponUsagesMock;

    /**
     * @var MockObject&Observer
     */
    private $observerMock;

    /**
     * @var MockObject
     */
    private $quoteMock;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
       // $this->updateCouponUsagesMock = $this->getMockForAbstractClass(UpdateCouponUsages::class);
        $this->observerMock = $this->getMockBuilder(Observer::class)
            ->addMethods(['getOrder', 'getQuote'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getCouponCode'])
            ->onlyMethods(['dataHasChangedFor'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->updateCouponUsagesMock = $this->getMockBuilder(UpdateCouponUsages::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();
        $this->couponUsagesIncrementObserver = new CouponUsagesIncrementObserver(
            $this->updateCouponUsagesMock
        );
    }

    /**
     * Testing the quote that doesn't have a coupon code set
     */
    public function testQuoteWithNoCouponCode()
    {
        $couponCode = 'coupon code';
        $this->observerMock->expects($this->once())->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->quoteMock->expects($this->once())->method('getCouponCode')
            ->willReturn(true);
        $this->quoteMock->expects($this->once())
            ->method('getCouponCode')
            ->willReturn($couponCode);
        $this->quoteMock->expects($this->any())
            ->method('dataHasChangedFor')
            ->with('coupon_code')
            ->willReturn(true);
        $this->observerMock->expects($this->once())->method('getOrder')
            ->willReturn($this->quoteMock);
        $this->updateCouponUsagesMock
            ->expects($this->once())
            ->method('execute');
        $this->couponUsagesIncrementObserver->execute($this->observerMock);
    }
}
