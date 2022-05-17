<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Creditmemo\Total;

use Magento\Directory\Model\Currency;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\Shipping;
use Magento\Tax\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShippingTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $creditmemoMock;

    /**
     * @var Config|MockObject
     */
    protected $taxConfig;

    /**
     * @var Shipping
     */
    protected $shippingCollector;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->creditmemoMock = $this->getMockBuilder(Creditmemo::class)
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

        $priceCurrencyMock = $this->getMockForAbstractClass(PriceCurrencyInterface::class);
        $priceCurrencyMock->expects($this->any())
            ->method('round')
            ->willReturnCallback(
                function ($amount) {
                    return round((float) $amount, 2);
                }
            );

        $this->taxConfig = $this->createMock(Config::class);

        $this->shippingCollector = $objectManager->getObject(
            Shipping::class,
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
     */
    public function testCollectException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Maximum shipping amount allowed to refund is: 5');
        $orderShippingAmount = 10;
        $orderShippingRefunded = 5;
        $allowedShippingAmount = $orderShippingAmount - $orderShippingRefunded;
        $desiredRefundAmount = $allowedShippingAmount + 1; // force amount to be larger than what is allowed

        $this->taxConfig->expects($this->any())->method('displaySalesShippingInclTax')->willReturn(false);

        $currencyMock = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->getMock();
        $currencyMock->expects($this->once())
            ->method('format')
            ->with($allowedShippingAmount, null, false)
            ->willReturn($allowedShippingAmount);

        $order = new DataObject(
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
     * @throws LocalizedException
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

        $order = new DataObject(
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

        $order = new DataObject(
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
     * @throws LocalizedException
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

        $order = new DataObject(
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
    /**
     * situation: The admin user did *not* specify any desired refund amount
     *
     * @throws LocalizedException
     */
    public function testCollectRefundShippingAmountIncTax()
    {
        $orderShippingAmount = 7.2300;
        $orderShippingRefunded = 7.2300;
        $allowedShippingAmount = $orderShippingAmount - $orderShippingRefunded;
        $baseOrderShippingAmount = 7.9500;
        $baseOrderShippingRefunded = 7.2300;
        $baseAllowedShippingAmount = $baseOrderShippingAmount - $baseOrderShippingRefunded;
        $shippingTaxAmount = 0;
        $shippingTaxAmountRefunded = 7.9500;
        $baseShippingTaxAmount = 0;
        $baseShippingTaxAmountRefunded = 0.7300;

        $expectedShippingAmountInclTax = $allowedShippingAmount + $shippingTaxAmount - $shippingTaxAmountRefunded;
        $expectedBaseShippingAmountInclTax =
            $baseAllowedShippingAmount + $baseShippingTaxAmount - $baseShippingTaxAmountRefunded;
        $expectedBaseShippingAmountInclTax = max($expectedBaseShippingAmountInclTax, 0);
        $grandTotalBefore = 14.35;
        $baseGrandTotalBefore = 14.35;
        $expectedGrandTotal = $grandTotalBefore + $allowedShippingAmount;
        $expectedBaseGrandTotal = $baseGrandTotalBefore + $baseAllowedShippingAmount;

        $this->taxConfig->expects($this->any())->method('displaySalesShippingInclTax')->willReturn(false);

        $order = new DataObject(
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
            ->with($expectedBaseGrandTotal)
            ->willReturnSelf();
        $this->shippingCollector->collect($this->creditmemoMock);
    }
}
