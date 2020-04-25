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

    public function tearDown(): void
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
            ->with($this->equalTo($totalAmount), $this->equalTo($expectedAdjustments))
            ->will($this->returnValue($amountBaseMock));

        $productMock = $this->getMockBuilder(SaleableInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPriceInfo', '__wakeup'])
            ->getMockForAbstractClass();

        $weeeAdjustmentMock = $this->getMockBuilder(AdjustmentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $weeeAdjustmentMock->expects($this->once())
            ->method('getAdjustmentCode')
            ->will($this->returnValue($weeeAdjustmentCode));
        $weeeAdjustmentMock->expects($this->once())
            ->method('isIncludedInBasePrice')
            ->will($this->returnValue(false));
        $weeeAdjustmentMock->expects($this->once())
            ->method('isIncludedInDisplayPrice')
            ->will($this->returnValue(true));
        $weeeAdjustmentMock->expects($this->once())
            ->method('applyAdjustment')
            ->with($this->equalTo($amountInclTax), $this->equalTo($productMock))
            ->will($this->returnValue($weeeAdjustment + $amountInclTax));

        $taxAdjustmentMock = $this->getMockBuilder(AdjustmentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $taxAdjustmentMock->expects($this->once())
            ->method('getAdjustmentCode')
            ->will($this->returnValue($taxAdjustmentCode));
        $taxAdjustmentMock->expects($this->once())
            ->method('isIncludedInBasePrice')
            ->will($this->returnValue(true));
        $taxAdjustmentMock->expects($this->once())
            ->method('extractAdjustment')
            ->with($this->equalTo($amountInclTax), $this->equalTo($productMock))
            ->will($this->returnValue($taxAdjustment));
        $taxAdjustmentMock->expects($this->once())
            ->method('applyAdjustment')
            ->with($this->equalTo($totalAmount), $this->equalTo($productMock))
            ->will($this->returnValue($totalAmount));
        $taxAdjustmentMock->expects($this->never())
            ->method('isIncludedInDisplayPrice');

        $adjustments = [$weeeAdjustmentMock, $taxAdjustmentMock];
        $priceInfoMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfoMock->expects($this->any())
            ->method('getAdjustments')
            ->will($this->returnValue($adjustments));

        $productMock->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfoMock));

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
            ->getMock();
        $taxAdjustmentMock->expects($this->once())
            ->method('getAdjustmentCode')
            ->will($this->returnValue($taxAdjustmentCode));
        $taxAdjustmentMock->expects($this->once())
            ->method('isIncludedInBasePrice')
            ->will($this->returnValue(true));
        $taxAdjustmentMock->expects($this->once())
            ->method('extractAdjustment')
            ->with($this->equalTo($amount), $this->equalTo($productMock))
            ->will($this->returnValue($adjustment));
        $taxAdjustmentMock->expects($this->once())
            ->method('applyAdjustment')
            ->with($this->equalTo($fullamount), $this->equalTo($productMock))
            ->will($this->returnValue($amount));

        $weeeAdjustmentMock = $this->getMockBuilder(AdjustmentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $weeeAdjustmentMock->expects($this->once())
            ->method('getAdjustmentCode')
            ->will($this->returnValue($weeeAdjustmentCode));
        $weeeAdjustmentMock->expects($this->once())
            ->method('isIncludedInBasePrice')
            ->will($this->returnValue(false));
        $weeeAdjustmentMock->expects($this->once())
            ->method('isIncludedInDisplayPrice')
            ->with($this->equalTo($productMock))
            ->will($this->returnValue(true));
        $weeeAdjustmentMock->expects($this->never())
            ->method('applyAdjustment');

        $adjustments = [$taxAdjustmentMock, $weeeAdjustmentMock];

        $priceInfoMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceInfo\Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfoMock->expects($this->any())
            ->method('getAdjustments')
            ->will($this->returnValue($adjustments));

        $productMock->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfoMock));

        $amountBaseMock = $this->getMockBuilder(Base::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->amountFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($amount), $this->equalTo($expectedAdjustments))
            ->will($this->returnValue($amountBaseMock));
        $result = $this->model->getAmount($amount, $productMock, true);
        $this->assertInstanceOf(AmountInterface::class, $result);
    }
}
