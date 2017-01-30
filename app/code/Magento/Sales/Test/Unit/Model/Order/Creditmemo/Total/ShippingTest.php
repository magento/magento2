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
     * @var \Magento\Tax\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxConfig;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\Total\Shipping
     */
    protected $shippingCollector;

    protected function setUp()
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

        $this->taxConfig = $this->getMock('Magento\Tax\Model\Config', [], [], '', false);

        $this->shippingCollector = $objectManager->getObject(
            'Magento\Sales\Model\Order\Creditmemo\Total\Shipping',
            [
                'priceCurrency' => $priceCurrencyMock,
            ]
        );

        // needed until 'taxConfig' becomes part of the constructor for shippingCollector
        $reflection = new \ReflectionClass(get_class($this->shippingCollector));
        $reflectionProperty = $reflection->getProperty('taxConfig');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->shippingCollector, $this->taxConfig);
    }

    /**
     * situation: The admin user specified a desired shipping refund that is greater than the amount allowed
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Maximum shipping amount allowed to refund is: 5
     */
    public function testCollectException()
    {
        $orderShippingAmount = 10;
        $orderShippingRefunded = 5;
        $allowedShippingAmount = $orderShippingAmount - $orderShippingRefunded;
        $desiredRefundAmount = $allowedShippingAmount + 1; // force amount to be larger than what is allowed

        $this->taxConfig->expects($this->any())->method('displaySalesShippingInclTax')->willReturn(false);

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
            ->willReturn($desiredRefundAmount);

        //expect to have an exception thrown
        $this->shippingCollector->collect($this->creditmemoMock);
    }

    /**
     * situation: The admin user did *not* specify any desired refund amount
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
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

        $this->taxConfig->expects($this->any())->method('displaySalesShippingInclTax')->willReturn(false);

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

        //determine expected partial refund amounts
        $desiredRefundAmount = $baseOrderShippingAmount * $ratio;

        $expectedShippingAmount = $orderShippingAmount * $ratio;
        $expectedShippingAmountInclTax = $orderShippingInclTax * $ratio;
        $expectedBaseShippingAmount = $baseOrderShippingAmount * $ratio;
        $expectedBaseShippingAmountInclTax = $baseOrderShippingInclTax * $ratio;

        $grandTotalBefore = 100;
        $baseGrandTotalBefore = 200;
        $expectedGrandTotal = $grandTotalBefore + $expectedShippingAmount;
        $expectedBaseGrandTtoal = $baseGrandTotalBefore + $expectedBaseShippingAmount;

        $this->taxConfig->expects($this->any())->method('displaySalesShippingInclTax')->willReturn(false);

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
            ->willReturn($desiredRefundAmount);
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

    /**
     * @return array
     */
    public function collectWithSpecifiedShippingAmountDataProvider()
    {
        return [
            'half' => [0.5], //This will test the case where specified amount equals maximum allowed amount
            'quarter' => [0.25],
        ];
    }

    /**
     * situation: The admin user specified the desired refund amount that has taxes embedded within it
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testCollectUsingTaxInclShippingAmount()
    {
        $this->taxConfig->expects($this->any())->method('displaySalesShippingInclTax')->willReturn(true);

        $orderShippingAmount = 15;
        $shippingTaxAmount = 3;
        $orderShippingInclTax = $orderShippingAmount + $shippingTaxAmount;
        $orderShippingAmountRefunded = 5;
        $shippingTaxRefunded = 1;
        $refundedInclTax = $orderShippingAmountRefunded + $shippingTaxRefunded;

        $currencyMultiple = 2;
        $baseOrderShippingAmount = $orderShippingAmount * $currencyMultiple;
        $baseShippingTaxAmount = $shippingTaxAmount * $currencyMultiple;
        $baseOrderShippingInclTax = $orderShippingInclTax * $currencyMultiple;
        $baseOrderShippingAmountRefunded = $orderShippingAmountRefunded * $currencyMultiple;
        $baseShippingTaxRefunded = $shippingTaxRefunded * $currencyMultiple;
        $baseRefundedInclTax = $refundedInclTax * $currencyMultiple;

        //determine expected amounts
        $desiredRefundAmount = $baseOrderShippingInclTax - $baseRefundedInclTax;

        $expectedShippingAmount = $orderShippingAmount - $orderShippingAmountRefunded;
        $expectedShippingAmountInclTax = $orderShippingInclTax - $refundedInclTax;

        $expectedBaseShippingAmount = $expectedShippingAmount * $currencyMultiple;
        $expectedBaseShippingAmountInclTax = $expectedShippingAmountInclTax * $currencyMultiple;

        $grandTotalBefore = 100;
        $baseGrandTotalBefore = 200;
        $expectedGrandTotal = $grandTotalBefore + $expectedShippingAmount;
        $expectedBaseGrandTtoal = $baseGrandTotalBefore + $expectedBaseShippingAmount;

        $order = new \Magento\Framework\DataObject(
            [
                'shipping_amount' => $orderShippingAmount,
                'base_shipping_amount' => $baseOrderShippingAmount,
                'shipping_refunded' => $orderShippingAmountRefunded,
                'base_shipping_refunded' => $baseOrderShippingAmountRefunded,
                'shipping_incl_tax' => $orderShippingInclTax,
                'base_shipping_incl_tax' => $baseOrderShippingInclTax,
                'shipping_tax_amount' => $shippingTaxAmount,
                'shipping_tax_refunded' => $shippingTaxRefunded,
                'base_shipping_tax_amount' => $baseShippingTaxAmount,
                'base_shipping_tax_refunded' => $baseShippingTaxRefunded,
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
            ->willReturn($desiredRefundAmount);
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
}
