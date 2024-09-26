<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Plugin\Model\Order\Invoice;

use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Plugin\Model\Order\Invoice\UpdateConfigurableProductTotalQty;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for class UpdateConfigurableProductTotalQty.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateConfigurableProductTotalQtyTest extends TestCase
{
    /**
     * @var UpdateConfigurableProductTotalQty
     */
    private $model;

    /**
     * @var ObjectManagerHelper|null
     */
    private $objectManagerHelper;

    /**
     * @var Invoice|MockObject
     */
    private $invoiceMock;

    /**
     * @var Order|MockObject
     */
    private $orderMock;

    /**
     * @var Item[]|MockObject
     */
    private $orderItemsMock;

    protected function setUp(): void
    {
        $this->invoiceMock = $this->createMock(Invoice::class);
        $this->orderMock = $this->createMock(Order::class);
        $this->orderItemsMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            UpdateConfigurableProductTotalQty::class,
            []
        );
    }

    /**
     * Test Set total quantity for configurable product invoice
     *
     * @param \Closure $orderItems
     * @param float $totalQty
     * @param float $productTotalQty
     * @dataProvider getOrdersForConfigurableProducts
     */
    public function testBeforeSetTotalQty(
        \Closure $orderItems,
        float $totalQty,
        float $productTotalQty
    ): void {
        $orderItems = $orderItems($this);
        $this->invoiceMock->expects($this->any())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->orderMock->expects($this->any())
            ->method('getAllItems')
            ->willReturn($orderItems);
        $expectedQty= $this->model->beforeSetTotalQty($this->invoiceMock, $totalQty);
        $this->assertEquals($expectedQty, $productTotalQty);
    }

    /**
     * DataProvider for beforeSetTotalQty.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public static function getOrdersForConfigurableProducts(): array
    {

        return [
            'verify productQty for simple products' => [
                'orderItems' => static fn (self $testCase) => $testCase->getOrderItems(
                    [
                        [
                            'parent_item_id' => null,
                            'product_type' => 'simple',
                            'qty_ordered' => 10
                        ]
                    ]
                ),
                'totalQty' => 10.00,
                'productTotalQty' => 10.00
            ],
            'verify productQty for configurable products' => [
                'orderItems' => static fn (self $testCase) => $testCase->getOrderItems(
                    [
                        [
                            'parent_item_id' => '2',
                            'product_type' => Configurable::TYPE_CODE,
                            'qty_ordered' => 10
                        ]
                    ]
                ),
                'totalQty' => 10.00,
                'productTotalQty' => 10.00
            ],
            'verify productQty for simple configurable products' => [
                'orderItems' => static fn (self $testCase) => $testCase->getOrderItems(
                    [
                        [
                            'parent_item_id' => null,
                            'product_type' => 'simple',
                            'qty_ordered' => 10
                        ],
                        [
                            'parent_item_id' => '2',
                            'product_type' => Configurable::TYPE_CODE,
                            'qty_ordered' => 10
                        ],
                        [
                            'parent_item_id' => '2',
                            'product_type' => Bundle::TYPE_CODE,
                            'qty_ordered' => 10
                        ]
                    ]
                ),
                'totalQty' => 30.00,
                'productTotalQty' => 30.00
            ]
        ];
    }

    /**
     * Get Order Items.
     *
     * @param array $orderItems
     * @return array
     */
    protected function getOrderItems(array $orderItems): array
    {
        $orderItemsMock = [];
        foreach ($orderItems as $key => $orderItem) {
            $orderItemsMock[$key] = $this->getMockBuilder(Item::class)
                ->disableOriginalConstructor()
                ->getMock();
            $orderItemsMock[$key]->expects($this->any())
                ->method('getParentItemId')
                ->willReturn($orderItem['parent_item_id']);
            $orderItemsMock[$key]->expects($this->any())
                ->method('getProductType')
                ->willReturn($orderItem['product_type']);
            $orderItemsMock[$key]->expects($this->any())
                ->method('getQtyOrdered')
                ->willReturn($orderItem['qty_ordered']);
        }
        return $orderItemsMock;
    }

    protected function tearDown(): void
    {
        unset($this->invoiceMock);
        unset($this->orderMock);
        unset($this->orderItemsMock);
    }
}
