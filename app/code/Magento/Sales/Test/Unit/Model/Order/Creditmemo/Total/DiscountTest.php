<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Creditmemo\Total;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Item;
use Magento\Sales\Model\Order\Creditmemo\Total\Cost;
use Magento\Sales\Model\Order\Creditmemo\Total\Discount;
use Magento\Tax\Model\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class DiscountTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Cost
     */
    protected $total;

    /**
     * @var Creditmemo|MockObject
     */
    protected $creditmemoMock;

    /**
     * @var Item|MockObject
     */
    protected $creditmemoItemMock;

    /**
     * @var Order|MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Sales\Model\Order\Item|MockObject
     */
    protected $orderItemMock;

    /**
     * @var Config|MockObject
     */
    private $taxConfig;

    protected function setUp(): void
    {
        $this->orderMock = $this->createPartialMock(
            Order::class,
            ['getBaseShippingDiscountAmount', 'getBaseShippingAmount', 'getShippingAmount', 'getBaseShippingRefunded']
        );
        $this->orderItemMock = $this->createPartialMockWithReflection(
            Order::class,
            [
                'isDummy', 'getQtyInvoiced', 'getQty', 'getQtyRefunded', 'getDiscountInvoiced',
                'getBaseDiscountInvoiced', 'getDiscountRefunded'
            ]
        );
        $this->creditmemoMock = $this->createPartialMockWithReflection(
            Creditmemo::class,
            [
                'setBaseCost', 'getAllItems', 'getOrder', 'getBaseShippingAmount', 'roundPrice',
                'setDiscountAmount', 'setBaseDiscountAmount', 'getBaseShippingInclTax',
                'getBaseShippingTaxAmount', 'hasBaseShippingAmount'
            ]
        );
        $this->creditmemoItemMock = $this->createPartialMockWithReflection(
            Item::class,
            [
                'getHasChildren', 'getBaseCost', 'getQty', 'getOrderItem', 'setDiscountAmount',
                'setBaseDiscountAmount', 'isLast'
            ]
        );
        $this->taxConfig = $this->createMock(Config::class);

        $this->total = new Discount($this->taxConfig);
    }

    public function testCollect()
    {
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('setDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('setBaseDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->creditmemoMock->expects($this->once())
            ->method('hasBaseShippingAmount')
            ->willReturn(true);
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseShippingAmount')
            ->willReturn(1);
        $this->orderMock->expects($this->once())
            ->method('getBaseShippingDiscountAmount')
            ->willReturn(1);
        $this->orderMock->expects($this->exactly(2))
            ->method('getBaseShippingAmount')
            ->willReturn(1);
        $this->orderMock->expects($this->once())
            ->method('getShippingAmount')
            ->willReturn(1);
        $this->creditmemoMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->creditmemoItemMock]);
        $this->creditmemoItemMock->expects($this->atLeastOnce())
            ->method('getOrderItem')
            ->willReturn($this->orderItemMock);
        $this->orderItemMock->expects($this->once())
            ->method('isDummy')
            ->willReturn(false);
        $this->orderItemMock->expects($this->once())
            ->method('getDiscountInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getBaseDiscountInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getQtyInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getDiscountRefunded')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getQtyRefunded')
            ->willReturn(0);
        $this->creditmemoItemMock->expects($this->once())
            ->method('isLast')
            ->willReturn(false);
        $this->creditmemoItemMock->expects($this->atLeastOnce())
            ->method('getQty')
            ->willReturn(1);
        $this->creditmemoItemMock->expects($this->exactly(1))
            ->method('setDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoItemMock->expects($this->exactly(1))
            ->method('setBaseDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('roundPrice')
            ->willReturnMap(
                [
                    [1, 'regular', true, 1],
                    [1, 'base', true, 1]
                ]
            );
        $this->assertEquals($this->total, $this->total->collect($this->creditmemoMock));
    }

    public function testCollectNoBaseShippingAmount()
    {
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('setDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('setBaseDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->creditmemoMock->expects($this->once())
            ->method('hasBaseShippingAmount')
            ->willReturn(false);
        $this->creditmemoMock->expects($this->never())
            ->method('getBaseShippingAmount');
        $this->creditmemoMock->expects($this->never())
            ->method('getBaseShippingInclTax');
        $this->creditmemoMock->expects($this->never())
            ->method('getBaseShippingTaxAmount');
        $this->orderMock->expects($this->once())
            ->method('getBaseShippingDiscountAmount')
            ->willReturn(1);
        $this->orderMock->expects($this->exactly(3))
            ->method('getBaseShippingAmount')
            ->willReturn(1);
        $this->orderMock->expects($this->once())
            ->method('getBaseShippingRefunded')
            ->willReturn(0);
        $this->orderMock->expects($this->once())
            ->method('getShippingAmount')
            ->willReturn(1);
        $this->creditmemoMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->creditmemoItemMock]);
        $this->creditmemoItemMock->expects($this->atLeastOnce())
            ->method('getOrderItem')
            ->willReturn($this->orderItemMock);
        $this->orderItemMock->expects($this->once())
            ->method('isDummy')
            ->willReturn(false);
        $this->orderItemMock->expects($this->once())
            ->method('getDiscountInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getBaseDiscountInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getQtyInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getDiscountRefunded')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getQtyRefunded')
            ->willReturn(0);
        $this->creditmemoItemMock->expects($this->once())
            ->method('isLast')
            ->willReturn(false);
        $this->creditmemoItemMock->expects($this->atLeastOnce())
            ->method('getQty')
            ->willReturn(1);
        $this->creditmemoItemMock->expects($this->exactly(1))
            ->method('setDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoItemMock->expects($this->exactly(1))
            ->method('setBaseDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('roundPrice')
            ->willReturnMap(
                [
                    [1, 'regular', true, 1],
                    [1, 'base', true, 1]
                ]
            );
        $this->assertEquals($this->total, $this->total->collect($this->creditmemoMock));
    }

    public function testCollectZeroShipping()
    {
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('setDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('setBaseDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->creditmemoMock->expects($this->once())
            ->method('hasBaseShippingAmount')
            ->willReturn(true);
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseShippingAmount')
            ->willReturn('0.0000');
        $this->orderMock->expects($this->never())
            ->method('getBaseShippingDiscountAmount');
        $this->orderMock->expects($this->never())
            ->method('getBaseShippingAmount');
        $this->orderMock->expects($this->never())
            ->method('getShippingAmount');
        $this->creditmemoMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->creditmemoItemMock]);
        $this->creditmemoItemMock->expects($this->atLeastOnce())
            ->method('getOrderItem')
            ->willReturn($this->orderItemMock);
        $this->orderItemMock->expects($this->once())
            ->method('isDummy')
            ->willReturn(false);
        $this->orderItemMock->expects($this->once())
            ->method('getDiscountInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getBaseDiscountInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getQtyInvoiced')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getDiscountRefunded')
            ->willReturn(1);
        $this->orderItemMock->expects($this->once())
            ->method('getQtyRefunded')
            ->willReturn(0);
        $this->creditmemoItemMock->expects($this->once())
            ->method('isLast')
            ->willReturn(false);
        $this->creditmemoItemMock->expects($this->atLeastOnce())
            ->method('getQty')
            ->willReturn(1);
        $this->creditmemoItemMock->expects($this->exactly(1))
            ->method('setDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoItemMock->expects($this->exactly(1))
            ->method('setBaseDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('roundPrice')
            ->willReturnMap(
                [
                    [1, 'regular', true, 1],
                    [1, 'base', true, 1]
                ]
            );
        $this->assertEquals($this->total, $this->total->collect($this->creditmemoMock));
    }

    public function testCollectNonZeroShipping()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('You can not refund shipping if there is no shipping amount.');
        $this->creditmemoMock->expects($this->once())
            ->method('setDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('setBaseDiscountAmount')
            ->willReturnSelf();
        $this->creditmemoMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->creditmemoMock->expects($this->once())
            ->method('hasBaseShippingAmount')
            ->willReturn(true);
        $this->creditmemoMock->expects($this->once())
            ->method('getBaseShippingAmount')
            ->willReturn('10.0000');
        $this->orderMock->expects($this->never())
            ->method('getBaseShippingDiscountAmount');
        $this->orderMock->expects($this->once())
            ->method('getBaseShippingAmount')
            ->willReturn('0.0000');
        $this->assertEquals($this->total, $this->total->collect($this->creditmemoMock));
    }

    /**
     * The shipping discount refunded follows the shipping the credit memo refunds
     *
     * The order has shipping 100 with a 40 shipping discount and 10% tax on shipping: shipping tax 6 is on the
     * discounted shipping and shipping incl. tax 110 on the full shipping, as the tax total stores them. The credit
     * memo data is what CreditmemoFactory leaves before collecting totals: the order's shipping tax fields, and the
     * requested shipping amount, if any, as both base_shipping_amount and base_shipping_incl_tax.
     *
     * @param bool $shippingInclTax
     * @param array $orderData
     * @param array $creditmemoData
     * @param float $expectedBaseDiscount
     * @return void
     */
    #[DataProvider('shippingDiscountDataProvider')]
    public function testCollectShippingDiscount(
        bool $shippingInclTax,
        array $orderData,
        array $creditmemoData,
        float $expectedBaseDiscount
    ): void {
        $this->taxConfig->method('displaySalesShippingInclTax')->willReturn($shippingInclTax);
        $order = $this->createPartialMock(Order::class, []);
        $order->setData(
            array_merge(
                [
                    'shipping_amount' => 100,
                    'base_shipping_amount' => 100,
                    'shipping_discount_amount' => 40,
                    'base_shipping_discount_amount' => 40,
                    'shipping_tax_amount' => 6,
                    'base_shipping_tax_amount' => 6,
                    'shipping_incl_tax' => 110,
                    'base_shipping_incl_tax' => 110,
                ],
                $orderData
            )
        );
        $creditmemo = $this->createPartialMock(Creditmemo::class, ['getOrder', 'getAllItems']);
        $creditmemo->method('getOrder')->willReturn($order);
        $creditmemo->method('getAllItems')->willReturn([]);
        $creditmemo->setData(
            array_merge(['base_shipping_incl_tax' => 110, 'base_shipping_tax_amount' => 6], $creditmemoData)
        );

        $this->total->collect($creditmemo);

        $this->assertEqualsWithDelta($expectedBaseDiscount, $creditmemo->getBaseDiscountAmount(), 0.0001);
        $this->assertEqualsWithDelta($expectedBaseDiscount, $creditmemo->getDiscountAmount(), 0.0001);
    }

    /**
     * @return array
     */
    public static function shippingDiscountDataProvider(): array
    {
        return [
            'no shipping refunded' => [
                false,
                [],
                ['base_shipping_amount' => 0, 'base_shipping_incl_tax' => 0],
                0.0,
            ],
            'part of the shipping refunded' => [
                false,
                [],
                ['base_shipping_amount' => 50, 'base_shipping_incl_tax' => 50],
                -20.0,
            ],
            'no shipping amount requested' => [
                false,
                [],
                [],
                -40.0,
            ],
            'no shipping amount requested, part of the shipping refunded before' => [
                false,
                ['base_shipping_refunded' => 25],
                [],
                -30.0,
            ],
            'no shipping amount requested, all of the shipping refunded before' => [
                false,
                ['base_shipping_refunded' => 100],
                [],
                0.0,
            ],
            'shipping incl. tax, no shipping refunded' => [
                true,
                [],
                ['base_shipping_amount' => 0, 'base_shipping_incl_tax' => 0],
                0.0,
            ],
            'shipping incl. tax, part of the shipping refunded' => [
                true,
                [],
                ['base_shipping_amount' => 55, 'base_shipping_incl_tax' => 55],
                -20.0,
            ],
            'shipping incl. tax, no shipping amount requested, part of the shipping refunded before' => [
                true,
                ['base_shipping_refunded' => 25],
                [],
                -30.0,
            ],
        ];
    }
}
