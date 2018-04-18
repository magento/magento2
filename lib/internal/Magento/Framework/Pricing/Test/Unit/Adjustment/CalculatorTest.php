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
class CalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Pricing\Adjustment\Calculator
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\Amount\AmountFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $amountFactoryMock;

    protected function setUp()
    {
        $this->amountFactoryMock = $this->getMockBuilder('Magento\Framework\Pricing\Amount\AmountFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new \Magento\Framework\Pricing\Adjustment\Calculator($this->amountFactoryMock);
    }

    public function tearDown()
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

        $amountBaseMock = $this->getMockBuilder('Magento\Framework\Pricing\Amount\Base')
            ->disableOriginalConstructor()
            ->getMock();
        $this->amountFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($totalAmount), $this->equalTo($expectedAdjustments))
            ->will($this->returnValue($amountBaseMock));

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['getPriceInfo', '__wakeup'])
            ->getMock();

        $weeeAdjustmentMock = $this->getMockBuilder('Magento\Weee\Pricing\Adjustment')
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

        $taxAdjustmentMock = $this->getMockBuilder('Magento\Tax\Pricing\Adjustment')
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
        $priceInfoMock = $this->getMockBuilder('\Magento\Framework\Pricing\PriceInfo\Base')
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfoMock->expects($this->any())
            ->method('getAdjustments')
            ->will($this->returnValue($adjustments));

        $productMock->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfoMock));

        $result = $this->model->getAmount($amountInclTax, $productMock);
        $this->assertInstanceOf('Magento\Framework\Pricing\Amount\AmountInterface', $result);
    }

    public function testGetAmountExclude()
    {
        $amount = 10;
        $fullamount = 10;
        $taxAdjustmentCode = 'tax';
        $weeeAdjustmentCode = 'weee';
        $adjustment = 5;
        $expectedAdjustments = [];

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['getPriceInfo', '__wakeup'])
            ->getMock();

        $taxAdjustmentMock = $this->getMockBuilder('Magento\Tax\Pricing\Adjustment')
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

        $weeeAdjustmentMock = $this->getMockBuilder('Magento\Weee\Pricing\Adjustment')
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

        $priceInfoMock = $this->getMockBuilder('\Magento\Framework\Pricing\PriceInfo\Base')
            ->disableOriginalConstructor()
            ->getMock();
        $priceInfoMock->expects($this->any())
            ->method('getAdjustments')
            ->will($this->returnValue($adjustments));

        $productMock->expects($this->any())
            ->method('getPriceInfo')
            ->will($this->returnValue($priceInfoMock));

        $amountBaseMock = $this->getMockBuilder('Magento\Framework\Pricing\Amount\Base')
            ->disableOriginalConstructor()
            ->getMock();

        $this->amountFactoryMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo($amount), $this->equalTo($expectedAdjustments))
            ->will($this->returnValue($amountBaseMock));
        $result = $this->model->getAmount($amount, $productMock, true);
        $this->assertInstanceOf('Magento\Framework\Pricing\Amount\AmountInterface', $result);
    }
}
