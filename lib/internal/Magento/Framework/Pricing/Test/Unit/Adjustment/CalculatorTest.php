<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Pricing\Test\Unit\Adjustment;

use Magento\Framework\Pricing\Adjustment\AdjustmentInterface;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\Amount\AmountFactory;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Amount\Base;
use Magento\Framework\Pricing\SaleableInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    /**
     * @var Calculator
     */
    protected $model;

    /**
     * @var AmountFactory|MockObject
     */
    protected $amountFactoryMock;

    protected function setUp(): void
    {
        $this->amountFactoryMock = $this->getMockBuilder(AmountFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new Calculator($this->amountFactoryMock);
    }

    protected function tearDown(): void
    {
        $this->model = null;
        $this->amountFactoryMock = null;
    }

    /**
     * Test getAmount()
     */
    public function testGetAmount()
    {
        $amountInclTax = 10;
        $taxAdjustment = 2;
        $weeeAdjustment = 5;
        $totalAmount = $amountInclTax + $weeeAdjustment;

        $weeeAdjustmentCode = 'weee';
        $taxAdjustmentCode = 'tax';
        $expectedAdjustments = [
            $weeeAdjustmentCode => $weeeAdjustment,
            $taxAdjustmentCode => $taxAdjustment,
        ];

        $amountBaseMock = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->amountFactoryMock->expects($this->once())
            ->method('create')
            ->with($totalAmount, $expectedAdjustments)
            ->willReturn($amountBaseMock);

        $productMock = $this->getMockBuilder(SaleableInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPriceInfo', '__wakeup'])
            ->getMockForAbstractClass();

        $weeeAdjustmentMock = $this->getMockBuilder(AdjustmentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $weeeAdjustmentMock->expects($this->once())
            ->method('getAdjustmentCode')
            ->willReturn($weeeAdjustmentCode);
        $weeeAdjustmentMock->expects($this->once())
            ->method('isIncludedInBasePrice')
            ->willReturn(false);
        $weeeAdjustmentMock->expects($this->once())
            ->method('isIncludedInDisplayPrice')
            ->willReturn(true);
        $weeeAdjustmentMock->expects($this->once())
            ->method('applyAdjustment')
            ->with($amountInclTax, $productMock)
            ->willReturn($weeeAdjustment + $amountInclTax);

        $taxAdjustmentMock = $this->getMockBuilder(AdjustmentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $taxAdjustmentMock->expects($this->once())
            ->method('getAdjustmentCode')
            ->willReturn($taxAdjustmentCode);
        $taxAdjustmentMock->expects($this->once())
            ->method('isIncludedInBasePrice')
            ->willReturn(true);
        $taxAdjustmentMock->expects($this->once())
            ->method('extractAdjustment')
            ->with($amountInclTax, $productMock)
            ->willReturn($taxAdjustment);
        $taxAdjustmentMock->expects($this->once())
            ->method('applyAdjustment')
            ->with($totalAmount, $productMock)
            ->willReturn($totalAmount);
        $taxAdjustmentMock->expects($this->never())
            ->method('isIncludedInDisplayPrice');

        $adjustments = [$weeeAdjustmentMock, $taxAdjustmentMock];
        $priceInfoMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfoMock->expects($this->any())
            ->method('getAdjustments')
            ->willReturn($adjustments);

        $productMock->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($priceInfoMock);

        $result = $this->model->getAmount($amountInclTax, $productMock);
        $this->assertInstanceOf(AmountInterface::class, $result);
    }

    public function testGetAmountExclude()
    {
        $amount = 10;
        $fullamount = 10;
        $taxAdjustmentCode = 'tax';
        $weeeAdjustmentCode = 'weee';
        $adjustment = 5;
        $expectedAdjustments = [];

        $productMock = $this->getMockBuilder(SaleableInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPriceInfo', '__wakeup'])
            ->getMockForAbstractClass();

        $taxAdjustmentMock = $this->getMockBuilder(AdjustmentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $taxAdjustmentMock->expects($this->once())
            ->method('getAdjustmentCode')
            ->willReturn($taxAdjustmentCode);
        $taxAdjustmentMock->expects($this->once())
            ->method('isIncludedInBasePrice')
            ->willReturn(true);
        $taxAdjustmentMock->expects($this->once())
            ->method('extractAdjustment')
            ->with($amount, $productMock)
            ->willReturn($adjustment);
        $taxAdjustmentMock->expects($this->once())
            ->method('applyAdjustment')
            ->with($fullamount, $productMock)
            ->willReturn($amount);

        $weeeAdjustmentMock = $this->getMockBuilder(AdjustmentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $weeeAdjustmentMock->expects($this->once())
            ->method('getAdjustmentCode')
            ->willReturn($weeeAdjustmentCode);
        $weeeAdjustmentMock->expects($this->once())
            ->method('isIncludedInBasePrice')
            ->willReturn(false);
        $weeeAdjustmentMock->expects($this->once())
            ->method('isIncludedInDisplayPrice')
            ->with($productMock)
            ->willReturn(true);
        $weeeAdjustmentMock->expects($this->never())
            ->method('applyAdjustment');

        $adjustments = [$taxAdjustmentMock, $weeeAdjustmentMock];

        $priceInfoMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfoMock->expects($this->any())
            ->method('getAdjustments')
            ->willReturn($adjustments);

        $productMock->expects($this->any())
            ->method('getPriceInfo')
            ->willReturn($priceInfoMock);

        $amountBaseMock = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->amountFactoryMock->expects($this->once())
            ->method('create')
            ->with($amount, $expectedAdjustments)
            ->willReturn($amountBaseMock);
        $result = $this->model->getAmount($amount, $productMock, true);
        $this->assertInstanceOf(AmountInterface::class, $result);
    }
}
