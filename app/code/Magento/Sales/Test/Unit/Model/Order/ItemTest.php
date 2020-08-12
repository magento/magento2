<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\OrderFactory as SalesOrderFactory;
use Magento\Sales\Model\ResourceModel\OrderFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for order item class.
 */
class ItemTest extends TestCase
{
    /**
     * @var Item
     */
    protected $model;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var OrderFactory|MockObject
     */
    protected $orderFactory;

    /**
     * @var Json|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->orderFactory = $this->createPartialMock(SalesOrderFactory::class, ['create']);

        $this->serializerMock = $this->getMockBuilder(Json::class)
            ->setMethods(['unserialize'])
            ->getMock();

        $arguments = [
            'orderFactory' => $this->orderFactory,
            'serializer' => $this->serializerMock
        ];
        $this->model = $this->objectManager->getObject(Item::class, $arguments);
    }

    public function testSetParentItemNull()
    {
        $this->assertEquals($this->model, $this->model->setParentItem(null));
        $this->assertNull($this->model->getParentItem());
    }

    public function testSetParentItem()
    {
        $item = $this->objectManager->getObject(Item::class, []);
        $this->assertEquals($this->model, $this->model->setParentItem($item));
        $this->assertEquals($item, $this->model->getParentItem());
        $this->assertTrue($item->getHasChildren());
        $this->assertCount(1, $item->getChildrenItems());
    }

    public function testGetPatentItem()
    {
        $item = $this->objectManager->getObject(Item::class, []);
        $this->model->setData(OrderItemInterface::PARENT_ITEM, $item);
        $this->assertEquals($item, $this->model->getParentItem());
    }

    public function testSetOrder()
    {
        $orderId = 123;
        $order = $this->createMock(Order::class);
        $order->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);
        $this->assertEquals($this->model, $this->model->setOrder($order));
        $this->assertEquals($orderId, $this->model->getOrderId());
    }

    public function testGetOrder()
    {
        //order and order_id was not set
        $this->assertNull($this->model->getOrder());

        //set order_id and get order by id
        $orderId = 123;
        $order = $this->createMock(Order::class);
        $order->expects($this->once())
            ->method('load')
            ->with($orderId)
            ->willReturnSelf();
        $this->orderFactory->expects($this->once())
            ->method('create')
            ->willReturn($order);
        $this->model->setOrderId($orderId);
        $this->assertEquals($order, $this->model->getOrder());

        //get existed order
        $this->assertEquals($order, $this->model->getOrder());
    }

    /**
     * @param $qtyBackOrdered
     * @param $hasChildren
     * @param $qtyCanceled
     * @param $qtyInvoiced
     * @param $qtyOrdered
     * @param $qtyRefunded
     * @param $qtyShipped
     * @param $expectedStatus
     *
     * @dataProvider getStatusIdDataProvider
     */
    public function testGetStatusId(
        $qtyBackOrdered,
        $qtyCanceled,
        $qtyInvoiced,
        $qtyOrdered,
        $qtyRefunded,
        $qtyShipped,
        $expectedStatus
    ) {
        $this->model->setQtyBackordered($qtyBackOrdered);
        $this->model->setQtyCanceled($qtyCanceled);
        $this->model->setQtyInvoiced($qtyInvoiced);
        $this->model->setQtyOrdered($qtyOrdered);
        $this->model->setQtyRefunded($qtyRefunded);
        $this->model->setQtyShipped($qtyShipped);

        $this->assertEquals($expectedStatus, $this->model->getStatusId());
    }

    /**
     * @return array
     */
    public function getStatusIdDataProvider()
    {
        return [
            [0, 0, 0, null, 0, 0, Item::STATUS_PENDING],
            [0, 10, 1, 100, 10, 80, Item::STATUS_SHIPPED],
            [1, 10, 1, 100, 10, 80, Item::STATUS_SHIPPED],
            [1, 10, 1, 100, 10, 99, Item::STATUS_MIXED],
            [0, 10, 80, 100, 10, 0, Item::STATUS_INVOICED],
            [1, 10, 80, 100, 10, 0, Item::STATUS_INVOICED],
            [1, 10, 99, 100, 10, 0, Item::STATUS_MIXED],
            [80, 10, null, 100, 10, null, Item::STATUS_BACKORDERED],
            [null, null, null, 9, 9, null, Item::STATUS_REFUNDED],
            [null, 9, null, 9, null, null, Item::STATUS_CANCELED],
            [1, 10, 70, 100, 10, 79, Item::STATUS_PARTIAL],
            [0, 10, 70, 100, 10, 79, Item::STATUS_PARTIAL]
        ];
    }

    public function testGetStatuses()
    {
        $statuses = [
            Item::STATUS_PENDING => 'Ordered',
            Item::STATUS_SHIPPED => 'Shipped',
            Item::STATUS_INVOICED => 'Invoiced',
            Item::STATUS_BACKORDERED => 'Backordered',
            Item::STATUS_RETURNED => 'Returned',
            Item::STATUS_REFUNDED => 'Refunded',
            Item::STATUS_CANCELED => 'Canceled',
            Item::STATUS_PARTIAL => 'Partial',
            Item::STATUS_MIXED => 'Mixed',
        ];
        $this->assertEquals($statuses, $this->model->getStatuses());
    }

    public function testGetOriginalPrice()
    {
        $price = 9.99;
        $this->model->setPrice($price);
        $this->assertEquals($price, $this->model->getOriginalPrice());

        $originalPrice = 5.55;
        $this->model->setData(OrderItemInterface::ORIGINAL_PRICE, $originalPrice);
        $this->assertEquals($originalPrice, $this->model->getOriginalPrice());
    }

    /**
     * Test get product options with serialization
     *
     * @param array|string $options
     * @param array $expectedResult
     *
     * @dataProvider getProductOptionsDataProvider
     */
    public function testGetProductOptions($options, $expectedResult)
    {
        if (is_string($options)) {
            $this->serializerMock->expects($this->once())
                ->method('unserialize')
                ->willReturn($expectedResult);
        }
        $this->model->setData('product_options', $options);
        $result = $this->model->getProductOptions();
        $this->assertSame($result, $expectedResult);
    }

    /**
     * Data provider for testGetProductOptions
     *
     * @return array
     */
    public function getProductOptionsDataProvider()
    {
        return [
            'array' => [
                'options' => [
                    'option1' => 'option 1 value',
                    'option2' => 'option 2 value',
                ],
                'expectedResult' => [
                    'option1' => 'option 1 value',
                    'option2' => 'option 2 value',
                ]
            ],
            'serialized' => [
                'options' => json_encode([
                    'option1' => 'option 1 value',
                    'option2' => 'option 2 value',
                ]),
                'expectedResult' => [
                    'option1' => 'option 1 value',
                    'option2' => 'option 2 value',
                ]
            ]
        ];
    }

    /**
     * Test different combinations of item qty setups
     *
     * @param array $options
     * @param float $expectedResult
     *
     * @dataProvider getItemQtyVariants
     */
    public function testGetSimpleQtyToMethods(array $options, $expectedResult)
    {
        $this->model->setData($options);
        $this->assertSame($this->model->getSimpleQtyToShip(), $expectedResult['to_ship']);
        $this->assertSame($this->model->getQtyToInvoice(), $expectedResult['to_invoice']);
    }

    /**
     * Provides different combinations of qty options for an item and the
     * expected qtys pending shipment and invoice
     *
     * @return array
     */
    public function getItemQtyVariants()
    {
        return [
            'empty_item' => [
                'options' => [
                    'qty_ordered' => 0, 'qty_invoiced' => 0, 'qty_refunded' => 0, 'qty_shipped' => 0,
                    'qty_canceled' => 0
                ],
                'expectedResult' => ['to_ship' => 0.0, 'to_invoice' => 0.0]
            ],
            'ordered_item' => [
                'options' => [
                    'qty_ordered' => 12, 'qty_invoiced' => 0, 'qty_refunded' => 0, 'qty_shipped' => 0,
                    'qty_canceled' => 0
                ],
                'expectedResult' => ['to_ship' => 12.0, 'to_invoice' => 12.0]
            ],
            'partially_invoiced' => [
                'options' => ['qty_ordered' => 12, 'qty_invoiced' => 4, 'qty_refunded' => 0, 'qty_shipped' => 0,
                    'qty_canceled' => 0,
                ],
                'expectedResult' => ['to_ship' => 12.0, 'to_invoice' => 8.0]
            ],
            'completely_invoiced' => [
                'options' => [
                    'qty_ordered' => 12, 'qty_invoiced' => 12, 'qty_refunded' => 0, 'qty_shipped' => 0,
                    'qty_canceled' => 0,
                ],
                'expectedResult' => ['to_ship' => 12.0, 'to_invoice' => 0.0]
            ],
            'partially_invoiced_refunded' => [
                'options' => [
                    'qty_ordered' => 12, 'qty_invoiced' => 5, 'qty_refunded' => 5, 'qty_shipped' => 0,
                    'qty_canceled' => 0,
                ],
                'expectedResult' => ['to_ship' => 7.0, 'to_invoice' => 7.0]
            ],
            'partially_refunded' => [
                'options' => [
                    'qty_ordered' => 12, 'qty_invoiced' => 12, 'qty_refunded' => 5, 'qty_shipped' => 0,
                    'qty_canceled' => 0,
                ],
                'expectedResult' => ['to_ship' => 7.0, 'to_invoice' => 0.0]
            ],
            'partially_shipped' => [
                'options' => [
                    'qty_ordered' => 12, 'qty_invoiced' => 0, 'qty_refunded' => 0, 'qty_shipped' => 4,
                    'qty_canceled' => 0
                ],
                'expectedResult' => ['to_ship' => 8.0, 'to_invoice' => 12.0]
            ],
            'partially_refunded_partially_shipped' => [
                'options' => [
                    'qty_ordered' => 12, 'qty_invoiced' => 12, 'qty_refunded' => 5, 'qty_shipped' => 4,
                    'qty_canceled' => 0
                ],
                'expectedResult' => ['to_ship' => 7.0, 'to_invoice' => 0.0]
            ],
            'complete' => [
                'options' => [
                    'qty_ordered' => 12, 'qty_invoiced' => 12, 'qty_refunded' => 0, 'qty_shipped' => 12,
                    'qty_canceled' => 0
                ],
                'expectedResult' => ['to_ship' => 0.0, 'to_invoice' => 0.0]
            ],
            'canceled' => [
                'options' => [
                    'qty_ordered' => 12, 'qty_invoiced' => 0, 'qty_refunded' => 0, 'qty_shipped' => 0,
                    'qty_canceled' => 12
                ],
                'expectedResult' => ['to_ship' => 0.0, 'to_invoice' => 0.0]
            ],
            'completely_shipped_using_decimals' => [
                'options' => [
                    'qty_ordered' => 4.4, 'qty_invoiced' => 0.4, 'qty_refunded' => 0.4, 'qty_shipped' => 4,
                    'qty_canceled' => 0,
                ],
                'expectedResult' => ['to_ship' => 0.4, 'to_invoice' => 4.0]
            ],
            'completely_invoiced_using_decimals' => [
                'options' => [
                    'qty_ordered' => 4.4, 'qty_invoiced' => 4, 'qty_refunded' => 0, 'qty_shipped' => 4,
                    'qty_canceled' => 0.4
                ],
                'expectedResult' => ['to_ship' => 0.0, 'to_invoice' => 0.0]
            ]
        ];
    }

    /**
     * Test getPrice() method returns float
     */
    public function testGetPriceReturnsFloat()
    {
        $price = 9.99;
        $this->model->setPrice($price);
        $this->assertEquals($price, $this->model->getPrice());
    }

    /**
     * Test getPrice() method returns null
     */
    public function testGetPriceReturnsNull()
    {
        $nullablePrice = null;
        $this->model->setData(OrderItemInterface::PRICE, $nullablePrice);
        $this->assertEquals($nullablePrice, $this->model->getPrice());
    }
}
