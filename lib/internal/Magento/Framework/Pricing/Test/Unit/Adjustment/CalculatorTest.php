<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Pricing\Test\Unit\Adjustment;

/**
 * Class CalculatorTest
 *
 */
class CalculatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $amountFactoryMock;

    protected function setUp(): void
    {
        $this->amountFactoryMock = $this->getMockBuilder(\Magento\Framework\Pricing\Amount\AmountFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new \Magento\Framework\Pricing\Adjustment\Calculator($this->amountFactoryMock);
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

        $amountBaseMock = $this->getMockBuilder(\Magento\Framework\Pricing\Amount\Base::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->amountFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($totalAmount), $this->equalTo($expectedAdjustments))
            ->willReturn($amountBaseMock);

        $productMock = $this->getMockBuilder(\Magento\Framework\Pricing\SaleableInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPriceInfo', '__wakeup'])
            ->getMockForAbstractClass();

        $weeeAdjustmentMock = $this->getMockBuilder(\Magento\Framework\Pricing\Adjustment\AdjustmentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
            ->with($this->equalTo($amountInclTax), $this->equalTo($productMock))
            ->willReturn($weeeAdjustment + $amountInclTax);

        $taxAdjustmentMock = $this->getMockBuilder(\Magento\Framework\Pricing\Adjustment\AdjustmentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $taxAdjustmentMock->expects($this->once())
            ->method('getAdjustmentCode')
            ->willReturn($taxAdjustmentCode);
        $taxAdjustmentMock->expects($this->once())
            ->method('isIncludedInBasePrice')
            ->willReturn(true);
        $taxAdjustmentMock->expects($this->once())
            ->method('extractAdjustment')
            ->with($this->equalTo($amountInclTax), $this->equalTo($productMock))
            ->willReturn($taxAdjustment);
        $taxAdjustmentMock->expects($this->once())
            ->method('applyAdjustment')
            ->with($this->equalTo($totalAmount), $this->equalTo($productMock))
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
        $this->assertInstanceOf(\Magento\Framework\Pricing\Amount\AmountInterface::class, $result);
    }

    public function testGetAmountExclude()
    {
        $amount = 10;
        $fullamount = 10;
        $taxAdjustmentCode = 'tax';
        $weeeAdjustmentCode = 'weee';
        $adjustment = 5;
        $expectedAdjustments = [];

        $productMock = $this->getMockBuilder(\Magento\Framework\Pricing\SaleableInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPriceInfo', '__wakeup'])
            ->getMockForAbstractClass();

        $taxAdjustmentMock = $this->getMockBuilder(\Magento\Framework\Pricing\Adjustment\AdjustmentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $taxAdjustmentMock->expects($this->once())
            ->method('getAdjustmentCode')
            ->willReturn($taxAdjustmentCode);
        $taxAdjustmentMock->expects($this->once())
            ->method('isIncludedInBasePrice')
            ->willReturn(true);
        $taxAdjustmentMock->expects($this->once())
            ->method('extractAdjustment')
            ->with($this->equalTo($amount), $this->equalTo($productMock))
            ->willReturn($adjustment);
        $taxAdjustmentMock->expects($this->once())
            ->method('applyAdjustment')
            ->with($this->equalTo($fullamount), $this->equalTo($productMock))
            ->willReturn($amount);

        $weeeAdjustmentMock = $this->getMockBuilder(\Magento\Framework\Pricing\Adjustment\AdjustmentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $weeeAdjustmentMock->expects($this->once())
            ->method('getAdjustmentCode')
            ->willReturn($weeeAdjustmentCode);
        $weeeAdjustmentMock->expects($this->once())
            ->method('isIncludedInBasePrice')
            ->willReturn(false);
        $weeeAdjustmentMock->expects($this->once())
            ->method('isIncludedInDisplayPrice')
            ->with($this->equalTo($productMock))
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

        $amountBaseMock = $this->getMockBuilder(\Magento\Framework\Pricing\Amount\Base::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->amountFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($amount), $this->equalTo($expectedAdjustments))
            ->willReturn($amountBaseMock);
        $result = $this->model->getAmount($amount, $productMock, true);
        $this->assertInstanceOf(\Magento\Framework\Pricing\Amount\AmountInterface::class, $result);
    }
}
