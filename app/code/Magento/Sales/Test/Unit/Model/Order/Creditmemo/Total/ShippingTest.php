<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Test\Unit\Model\Order\Creditmemo\Total;

class ShippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoMock;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\Total\Shipping
     */
    protected $shippingCollector;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->creditmemoMock = $this->getMockBuilder('Magento\Sales\Model\Order\Creditmemo')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getOrder',
                    'hasBaseShippingAmount',
                    'getBaseShippingAmount',
                    'setShippingAmount',
                    'setBaseShippingAmount',
                    'setShippingInclTax',
                    'setBaseShippingInclTax',
                    'setGrandTotal',
                    'setBaseGrandTotal',
                    'getGrandTotal',
                    'getBaseGrandTotal',
                ]
            )->getMock();

        $priceCurrencyMock = $this->getMock('Magento\Framework\Pricing\PriceCurrencyInterface');
        $priceCurrencyMock->expects($this->any())
            ->method('round')
            ->willReturnCallback(
                function ($amount) {
                    return round($amount, 2);
                }
            );

        $this->shippingCollector = $objectManager->getObject(
            'Magento\Sales\Model\Order\Creditmemo\Total\Shipping',
            [
                'priceCurrency' => $priceCurrencyMock,
            ]
        );
    }

    /**
     * Test the case where shipping amount specified is greater than shipping amount allowed
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Maximum shipping amount allowed to refund is: 5
     */
    public function testCollectException()
    {
        $orderShippingAmount = 10;
        $orderShippingRefunded = 5;
        $allowedShippingAmount = $orderShippingAmount - $orderShippingRefunded;
        $creditmemoShippingAmount = 6;
        $currencyMock = $this->getMockBuilder('Magento\Directory\Model\Currency')
            ->disableOriginalConstructor()
            ->getMock();
        $currencyMock->expects($this->once())
            ->method('format')
            ->with($allowedShippingAmount, null, false)
            ->willReturn($allowedShippingAmount);
        $order = new \Magento\Framework\DataObject(
            [
                'base_shipping_amount' => $orderShippingAmount,
                'base_shipping_refunded' => $orderShippingRefunded,
                'base_currency' => $currencyMock,
            ]
        );

        $this->creditmemoMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($order);

        $this->creditmemoMock->expects($this->once())
            ->method('hasBaseShippingAmount')
            ->willReturn(true);
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseShippingAmount')
            ->willReturn($creditmemoShippingAmount);


        $this->shippingCollector->collect($this->creditmemoMock);
    }

    public function testCollectNoSpecifiedShippingAmount()
    {
        $orderShippingAmount = 10;
        $orderShippingRefunded = 5;
        $allowedShippingAmount = $orderShippingAmount - $orderShippingRefunded;
        $baseOrderShippingAmount = 20;
        $baseOrderShippingRefunded = 10;
        $baseAllowedShippingAmount = $baseOrderShippingAmount - $baseOrderShippingRefunded;
        $shippingTaxAmount = 2;
        $shippingTaxAmountRefunded = 1;
        $baseShippingTaxAmount = 4;
        $baseShippingTaxAmountRefunded = 2;

        $expectedShippingAmountInclTax = $allowedShippingAmount + $shippingTaxAmount - $shippingTaxAmountRefunded;
        $expectedBaseShippingAmountInclTax =
            $baseAllowedShippingAmount + $baseShippingTaxAmount - $baseShippingTaxAmountRefunded;
        $grandTotalBefore = 100;
        $baseGrandTotalBefore = 200;
        $expectedGrandTotal = $grandTotalBefore + $allowedShippingAmount;
        $expectedBaseGrandTtoal = $baseGrandTotalBefore + $baseAllowedShippingAmount;

        $order = new \Magento\Framework\DataObject(
            [
                'shipping_amount' => $orderShippingAmount,
                'shipping_refunded' => $orderShippingRefunded,
                'base_shipping_amount' => $baseOrderShippingAmount,
                'base_shipping_refunded' => $baseOrderShippingRefunded,
                'shipping_incl_tax' => $orderShippingAmount + $shippingTaxAmount,
                'base_shipping_incl_tax' => $baseOrderShippingAmount + $baseShippingTaxAmount,
                'shipping_tax_amount' => $shippingTaxAmount,
                'shipping_tax_refunded' => $shippingTaxAmountRefunded,
                'base_shipping_tax_amount' => $baseShippingTaxAmount,
                'base_shipping_tax_refunded' => $baseShippingTaxAmountRefunded,
            ]
        );

        $this->creditmemoMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($order);

        $this->creditmemoMock->expects($this->once())
            ->method('hasBaseShippingAmount')
            ->willReturn(false);

        $this->creditmemoMock->expects($this->once())
            ->method('getGrandTotal')
            ->willReturn($grandTotalBefore);
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseGrandTotal')
            ->willReturn($baseGrandTotalBefore);

        //verify
        $this->creditmemoMock->expects($this->once())
            ->method('setShippingAmount')
            ->with($allowedShippingAmount)
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('setBaseShippingAmount')
            ->with($baseAllowedShippingAmount)
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('setShippingInclTax')
            ->with($expectedShippingAmountInclTax)
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('setBaseShippingInclTax')
            ->with($expectedBaseShippingAmountInclTax)
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('setGrandTotal')
            ->with($expectedGrandTotal)
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('setBaseGrandTotal')
            ->with($expectedBaseGrandTtoal)
            ->willReturnSelf();

        $this->shippingCollector->collect($this->creditmemoMock);
    }

    /**
     * @param float $ratio
     * @dataProvider collectWithSpecifiedShippingAmountDataProvider
     */
    public function testCollectWithSpecifiedShippingAmount($ratio)
    {
        $orderShippingAmount = 10;
        $orderShippingAmountRefunded = 5;
        $baseOrderShippingAmount = 20;
        $baseOrderShippingAmountRefunded = 10;
        $shippingTaxAmount = 2;
        $baseShippingTaxAmount = 4;
        $orderShippingInclTax = $orderShippingAmount + $shippingTaxAmount;
        $baseOrderShippingInclTax = $baseOrderShippingAmount + $baseShippingTaxAmount;

        //refund half
        $creditmemoBaseShippingAmount = $ratio * $baseOrderShippingAmount;

        $expectedShippingAmount = $orderShippingAmount * $ratio;
        $expectedShippingAmountInclTax = $orderShippingInclTax * $ratio;
        $expectedBaseShippingAmount = $baseOrderShippingAmount * $ratio;
        $expectedBaseShippingAmountInclTax = $baseOrderShippingInclTax * $ratio;

        $grandTotalBefore = 100;
        $baseGrandTotalBefore = 200;
        $expectedGrandTotal = $grandTotalBefore + $expectedShippingAmount;
        $expectedBaseGrandTtoal = $baseGrandTotalBefore + $expectedBaseShippingAmount;

        $order = new \Magento\Framework\DataObject(
            [
                'shipping_amount' => $orderShippingAmount,
                'shipping_refunded' => $orderShippingAmountRefunded,
                'base_shipping_amount' => $baseOrderShippingAmount,
                'base_shipping_refunded' => $baseOrderShippingAmountRefunded,
                'shipping_incl_tax' => $orderShippingInclTax,
                'base_shipping_incl_tax' => $baseOrderShippingInclTax,
            ]
        );

        $this->creditmemoMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($order);

        $this->creditmemoMock->expects($this->once())
            ->method('hasBaseShippingAmount')
            ->willReturn(true);
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseShippingAmount')
            ->willReturn($creditmemoBaseShippingAmount);

        $this->creditmemoMock->expects($this->once())
            ->method('getGrandTotal')
            ->willReturn($grandTotalBefore);
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseGrandTotal')
            ->willReturn($baseGrandTotalBefore);

        //verify
        $this->creditmemoMock->expects($this->once())
            ->method('setShippingAmount')
            ->with($expectedShippingAmount)
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('setBaseShippingAmount')
            ->with($expectedBaseShippingAmount)
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('setShippingInclTax')
            ->with($expectedShippingAmountInclTax)
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('setBaseShippingInclTax')
            ->with($expectedBaseShippingAmountInclTax)
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('setGrandTotal')
            ->with($expectedGrandTotal)
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('setBaseGrandTotal')
            ->with($expectedBaseGrandTtoal)
            ->willReturnSelf();

        $this->shippingCollector->collect($this->creditmemoMock);
    }

    public function collectWithSpecifiedShippingAmountDataProvider()
    {
        return [
            'half' => [0.5], //This will test the case where specified amount equals maximum allowed amount
            'quarter' => [0.25],
        ];
    }
}
