<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\OrderFactory as SalesOrderFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Catalog\Model\Product\Type\AbstractType as ProductAbstractType;
use Magento\Framework\DataObject;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Unit test for order item class.
 *
 * @suppressWarnings(PHPMD.CouplingBetweenObjects)
 * @suppressWarnings(PHPMD.ExcessivePublicCount)
 */
class ItemTest extends TestCase
{
    use MockCreationTrait;
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
            ->onlyMethods(['unserialize'])
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

        // Create a mock factory for this specific test
        $mockFactory = $this->createPartialMock(SalesOrderFactory::class, ['create']);
        $mockFactory->expects($this->once())
            ->method('create')
            ->willReturn($order);

        // Create a new model instance with the mock factory
        $testModel = $this->objectManager->getObject(Item::class, [
            'orderFactory' => $mockFactory,
            'serializer' => $this->serializerMock
        ]);
        $testModel->setOrderId($orderId);
        $this->assertEquals($order, $testModel->getOrder());

        //get existed order
        $this->assertEquals($order, $testModel->getOrder());
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
     *     */
    #[DataProvider('getStatusIdDataProvider')]
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
    public static function getStatusIdDataProvider()
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
     *     */
    #[DataProvider('getProductOptionsDataProvider')]
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
    public static function getProductOptionsDataProvider()
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
     *     */
    #[DataProvider('getItemQtyVariants')]
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
    public static function getItemQtyVariants()
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
                'expectedResult' => ['to_ship' => 3.0, 'to_invoice' => 0.0]
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
                    'qty_ordered' => 4.8, 'qty_invoiced' => 0.4, 'qty_refunded' => 0.4, 'qty_shipped' => 4,
                    'qty_canceled' => 0,
                ],
                'expectedResult' => ['to_ship' => 0.4, 'to_invoice' => 4.4]
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

    /**
     * Test canInvoice method
     *
     * @param array $data
     * @param bool $expected
     */
    #[DataProvider('canInvoiceDataProvider')]
    public function testCanInvoice(array $data, bool $expected)
    {
        $this->model->setData($data);
        $this->assertEquals($expected, $this->model->canInvoice());
    }

    /**
     * Data provider for testCanInvoice
     *
     * @return array
     */
    public static function canInvoiceDataProvider()
    {
        return [
            'can_invoice' => [
                'data' => ['qty_ordered' => 10, 'qty_invoiced' => 5, 'qty_canceled' => 0],
                'expected' => true
            ],
            'cannot_invoice' => [
                'data' => ['qty_ordered' => 10, 'qty_invoiced' => 10, 'qty_canceled' => 0],
                'expected' => false
            ],
            'nothing_to_invoice' => [
                'data' => ['qty_ordered' => 10, 'qty_invoiced' => 5, 'qty_canceled' => 5],
                'expected' => false
            ]
        ];
    }

    /**
     * Test canShip method
     *
     * @param array $data
     * @param bool $expected
     */
    #[DataProvider('canShipDataProvider')]
    public function testCanShip(array $data, bool $expected)
    {
        $this->model->setData($data);
        $this->assertEquals($expected, $this->model->canShip());
    }

    /**
     * Data provider for testCanShip
     *
     * @return array
     */
    public static function canShipDataProvider()
    {
        return [
            'can_ship' => [
                'data' => ['qty_ordered' => 10, 'qty_shipped' => 5, 'qty_refunded' => 0, 'qty_canceled' => 0],
                'expected' => true
            ],
            'cannot_ship' => [
                'data' => ['qty_ordered' => 10, 'qty_shipped' => 10, 'qty_refunded' => 0, 'qty_canceled' => 0],
                'expected' => false
            ],
            'nothing_to_ship' => [
                'data' => ['qty_ordered' => 10, 'qty_shipped' => 5, 'qty_refunded' => 3, 'qty_canceled' => 2],
                'expected' => false
            ]
        ];
    }

    /**
     * Test canRefund method
     *
     * @param array $data
     * @param bool $expected
     */
    #[DataProvider('canRefundDataProvider')]
    public function testCanRefund(array $data, bool $expected)
    {
        $this->model->setData($data);
        $this->assertEquals($expected, $this->model->canRefund());
    }

    /**
     * Data provider for testCanRefund
     *
     * @return array
     */
    public static function canRefundDataProvider()
    {
        return [
            'can_refund' => [
                'data' => ['qty_invoiced' => 10, 'qty_refunded' => 5],
                'expected' => true
            ],
            'cannot_refund' => [
                'data' => ['qty_invoiced' => 10, 'qty_refunded' => 10],
                'expected' => false
            ],
            'nothing_invoiced' => [
                'data' => ['qty_invoiced' => 0, 'qty_refunded' => 0],
                'expected' => false
            ]
        ];
    }

    /**
     * Test getQtyToRefund method
     *
     * @param array $data
     * @param float $expected
     */
    #[DataProvider('getQtyToRefundDataProvider')]
    public function testGetQtyToRefund(array $data, float $expected)
    {
        $this->model->setData($data);
        $this->assertEquals($expected, $this->model->getQtyToRefund());
    }

    /**
     * Data provider for testGetQtyToRefund
     *
     * @return array
     */
    public static function getQtyToRefundDataProvider()
    {
        return [
            'partial_refund' => [
                'data' => ['qty_invoiced' => 10, 'qty_refunded' => 3],
                'expected' => 7.0
            ],
            'full_refund' => [
                'data' => ['qty_invoiced' => 10, 'qty_refunded' => 10],
                'expected' => 0.0
            ],
            'no_refund' => [
                'data' => ['qty_invoiced' => 10, 'qty_refunded' => 0],
                'expected' => 10.0
            ]
        ];
    }

    /**
     * Test getQtyToCancel method
     */
    public function testGetQtyToCancel()
    {
        $this->model->setData([
            'qty_ordered' => 10,
            'qty_invoiced' => 2,
            'qty_shipped' => 3,
            'qty_canceled' => 0
        ]);
        // Min of getQtyToInvoice (10-2-0=8) and getQtyToShip (10-3-0-0=7) = 7
        $this->assertEquals(7.0, $this->model->getQtyToCancel());
    }

    /**
     * Test getStatus method
     */
    public function testGetStatus()
    {
        $this->model->setData([
            'qty_ordered' => 10,
            'qty_shipped' => 10,
            'qty_invoiced' => 10,
            'qty_canceled' => 0,
            'qty_refunded' => 0
        ]);
        $this->assertEquals('Shipped', $this->model->getStatus());
    }

    /**
     * Test getStatusName static method
     */
    public function testGetStatusName()
    {
        $this->assertEquals('Ordered', Item::getStatusName(Item::STATUS_PENDING));
        $this->assertEquals('Shipped', Item::getStatusName(Item::STATUS_SHIPPED));
        $this->assertEquals('Invoiced', Item::getStatusName(Item::STATUS_INVOICED));
        $this->assertEquals('Canceled', Item::getStatusName(Item::STATUS_CANCELED));
        $this->assertEquals('Unknown Status', Item::getStatusName(999));
    }

    /**
     * Test cancel method
     */
    public function testCancel()
    {
        $this->model->setData([
            'qty_ordered' => 10,
            'qty_invoiced' => 0,
            'qty_shipped' => 0,
            'qty_canceled' => 0,
            'base_tax_amount' => 5,
            'discount_tax_compensation_amount' => 2
        ]);

        $this->model->cancel();
        $this->assertEquals(10.0, $this->model->getQtyCanceled());
    }

    /**
     * Test cancel when already canceled
     */
    public function testCancelWhenAlreadyCanceled()
    {
        $this->model->setData([
            'qty_ordered' => 10,
            'qty_canceled' => 10,
            'qty_invoiced' => 0,
            'qty_shipped' => 0
        ]);

        $this->model->cancel();
        // Should remain 10, not double cancel
        $this->assertEquals(10, $this->model->getQtyCanceled());
    }

    /**
     * Test setProductOptions method
     */
    public function testSetProductOptions()
    {
        $options = ['option1' => 'value1', 'option2' => 'value2'];
        $result = $this->model->setProductOptions($options);

        $this->assertSame($this->model, $result);
        $this->assertEquals($options, $this->model->getProductOptions());
    }

    /**
     * Test getProductOptionByCode method
     *
     * @param string|null $code
     * @param mixed $expected
     */
    #[DataProvider('getProductOptionByCodeDataProvider')]
    public function testGetProductOptionByCode($code, $expected)
    {
        $options = [
            'option1' => 'value1',
            'option2' => 'value2',
            'real_product_type' => 'configurable'
        ];
        $this->model->setProductOptions($options);

        $result = $this->model->getProductOptionByCode($code);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for testGetProductOptionByCode
     *
     * @return array
     */
    public static function getProductOptionByCodeDataProvider()
    {
        return [
            'get_specific_option' => ['option1', 'value1'],
            'get_another_option' => ['option2', 'value2'],
            'get_non_existent_option' => ['option3', null],
            'get_all_options' => [
                null,
                ['option1' => 'value1', 'option2' => 'value2', 'real_product_type' => 'configurable']
            ]
        ];
    }

    /**
     * Test getRealProductType method
     */
    public function testGetRealProductType()
    {
        $this->model->setProductOptions(['real_product_type' => 'configurable']);
        $this->assertEquals('configurable', $this->model->getRealProductType());
    }

    /**
     * Test getRealProductType returns null when not set
     */
    public function testGetRealProductTypeReturnsNull()
    {
        $this->model->setProductOptions([]);
        $this->assertNull($this->model->getRealProductType());
    }

    /**
     * Test addChildItem with single item
     */
    public function testAddChildItemWithSingleItem()
    {
        $childItem = $this->objectManager->getObject(Item::class, []);
        $this->model->addChildItem($childItem);

        $children = $this->model->getChildrenItems();
        $this->assertCount(1, $children);
        $this->assertSame($childItem, $children[0]);
    }

    /**
     * Test addChildItem with array of items
     */
    public function testAddChildItemWithArray()
    {
        $childItem1 = $this->objectManager->getObject(Item::class, []);
        $childItem2 = $this->objectManager->getObject(Item::class, []);

        $this->model->addChildItem([$childItem1, $childItem2]);

        $children = $this->model->getChildrenItems();
        $this->assertCount(2, $children);
    }

    /**
     * Test getChildrenItems returns empty array by default
     */
    public function testGetChildrenItemsEmpty()
    {
        $this->assertIsArray($this->model->getChildrenItems());
        $this->assertEmpty($this->model->getChildrenItems());
    }

    /**
     * Test isChildrenCalculated method
     *
     * @param bool $hasParent
     * @param int|null $calculation
     * @param bool $expected
     */
    #[DataProvider('isChildrenCalculatedDataProvider')]
    public function testIsChildrenCalculated(bool $hasParent, ?int $calculation, bool $expected)
    {
        if ($hasParent) {
            $parentItem = $this->objectManager->getObject(Item::class, []);
            if ($calculation !== null) {
                $parentItem->setProductOptions(['product_calculations' => $calculation]);
            }
            $this->model->setParentItem($parentItem);
        } else {
            if ($calculation !== null) {
                $this->model->setProductOptions(['product_calculations' => $calculation]);
            }
        }

        $this->assertEquals($expected, $this->model->isChildrenCalculated());
    }

    /**
     * Data provider for testIsChildrenCalculated
     *
     * @return array
     */
    public static function isChildrenCalculatedDataProvider()
    {
        return [
            'no_parent_calculate_child' => [
                false,
                ProductAbstractType::CALCULATE_CHILD,
                true
            ],
            'no_parent_calculate_parent' => [
                false,
                ProductAbstractType::CALCULATE_PARENT,
                false
            ],
            'no_parent_no_calculation' => [false, null, false],
            'has_parent_calculate_child' => [
                true,
                ProductAbstractType::CALCULATE_CHILD,
                true
            ],
            'has_parent_calculate_parent' => [
                true,
                ProductAbstractType::CALCULATE_PARENT,
                false
            ]
        ];
    }

    /**
     * Test isShipSeparately method
     *
     * @param bool $hasParent
     * @param int|null $shipmentType
     * @param bool $expected
     */
    #[DataProvider('isShipSeparatelyDataProvider')]
    public function testIsShipSeparately(bool $hasParent, ?int $shipmentType, bool $expected)
    {
        if ($hasParent) {
            $parentItem = $this->objectManager->getObject(Item::class, []);
            if ($shipmentType !== null) {
                $parentItem->setProductOptions(['shipment_type' => $shipmentType]);
            }
            $this->model->setParentItem($parentItem);
        } else {
            if ($shipmentType !== null) {
                $this->model->setProductOptions(['shipment_type' => $shipmentType]);
            }
        }

        $this->assertEquals($expected, $this->model->isShipSeparately());
    }

    /**
     * Data provider for testIsShipSeparately
     *
     * @return array
     */
    public static function isShipSeparatelyDataProvider()
    {
        return [
            'no_parent_ship_separately' => [
                false,
                ProductAbstractType::SHIPMENT_SEPARATELY,
                true
            ],
            'no_parent_ship_together' => [
                false,
                ProductAbstractType::SHIPMENT_TOGETHER,
                false
            ],
            'no_parent_no_shipment_type' => [false, null, false],
            'has_parent_ship_separately' => [
                true,
                ProductAbstractType::SHIPMENT_SEPARATELY,
                true
            ],
            'has_parent_ship_together' => [
                true,
                ProductAbstractType::SHIPMENT_TOGETHER,
                false
            ]
        ];
    }

    /**
     * Test isDummy method for shipment scenarios
     *
     * @param array $setup
     * @param bool $expected
     */
    #[DataProvider('isDummyShipmentDataProvider')]
    public function testIsDummyForShipment(array $setup, bool $expected)
    {
        if (isset($setup['has_children'])) {
            $this->model->setHasChildren($setup['has_children']);
        }
        if (isset($setup['ship_separately'])) {
            $this->model->setProductOptions([
                'shipment_type' => $setup['ship_separately']
                    ? ProductAbstractType::SHIPMENT_SEPARATELY
                    : ProductAbstractType::SHIPMENT_TOGETHER
            ]);
        }
        if (isset($setup['has_parent'])) {
            $parentItem = $this->objectManager->getObject(Item::class, []);
            $this->model->setParentItem($parentItem);
            if (isset($setup['parent_ship_separately'])) {
                $parentItem->setProductOptions([
                    'shipment_type' => $setup['parent_ship_separately']
                        ? ProductAbstractType::SHIPMENT_SEPARATELY
                        : ProductAbstractType::SHIPMENT_TOGETHER
                ]);
            }
        }

        $this->assertEquals($expected, $this->model->isDummy(true));
    }

    /**
     * Data provider for testIsDummyForShipment
     *
     * @return array
     */
    public static function isDummyShipmentDataProvider()
    {
        return [
            'has_children_ship_separately' => [
                ['has_children' => true, 'ship_separately' => true],
                true
            ],
            'has_children_ship_together' => [
                ['has_children' => true, 'ship_separately' => false],
                false
            ],
            'has_parent_ship_separately' => [
                ['has_parent' => true, 'parent_ship_separately' => true],
                false
            ],
            'has_parent_ship_together' => [
                ['has_parent' => true, 'parent_ship_separately' => false],
                true
            ],
            'simple_item' => [[], false]
        ];
    }

    /**
     * Test isDummy method for calculation scenarios
     *
     * @param array $setup
     * @param bool $expected
     */
    #[DataProvider('isDummyCalculationDataProvider')]
    public function testIsDummyForCalculation(array $setup, bool $expected)
    {
        if (isset($setup['has_children'])) {
            $this->model->setHasChildren($setup['has_children']);
        }
        if (isset($setup['children_calculated'])) {
            $this->model->setProductOptions([
                'product_calculations' => $setup['children_calculated']
                    ? ProductAbstractType::CALCULATE_CHILD
                    : ProductAbstractType::CALCULATE_PARENT
            ]);
        }
        if (isset($setup['has_parent'])) {
            $parentItem = $this->objectManager->getObject(Item::class, []);
            $this->model->setParentItem($parentItem);
            if (isset($setup['parent_children_calculated'])) {
                $parentItem->setProductOptions([
                    'product_calculations' => $setup['parent_children_calculated']
                        ? ProductAbstractType::CALCULATE_CHILD
                        : ProductAbstractType::CALCULATE_PARENT
                ]);
            }
        }

        $this->assertEquals($expected, $this->model->isDummy(false));
    }

    /**
     * Data provider for testIsDummyForCalculation
     *
     * @return array
     */
    public static function isDummyCalculationDataProvider()
    {
        return [
            'has_children_calculate_child' => [
                ['has_children' => true, 'children_calculated' => true],
                true
            ],
            'has_children_calculate_parent' => [
                ['has_children' => true, 'children_calculated' => false],
                false
            ],
            'has_parent_calculate_child' => [
                ['has_parent' => true, 'parent_children_calculated' => true],
                false
            ],
            'has_parent_calculate_parent' => [
                ['has_parent' => true, 'parent_children_calculated' => false],
                true
            ],
            'simple_item' => [[], false]
        ];
    }

    /**
     * Test getBuyRequest method
     */
    public function testGetBuyRequest()
    {
        $this->model->setProductOptions([
            'info_buyRequest' => ['qty' => 5, 'product' => 123]
        ]);
        $this->model->setQtyOrdered(10);

        $buyRequest = $this->model->getBuyRequest();

        $this->assertInstanceOf(DataObject::class, $buyRequest);
        $this->assertEquals(10, $buyRequest->getQty());
        $this->assertEquals(123, $buyRequest->getProduct());
    }

    /**
     * Test getBuyRequest when no info_buyRequest exists
     */
    public function testGetBuyRequestEmpty()
    {
        $this->model->setQtyOrdered(5);
        $buyRequest = $this->model->getBuyRequest();

        $this->assertInstanceOf(DataObject::class, $buyRequest);
        $this->assertEquals(5, $buyRequest->getQty());
    }

    /**
     * Test getQtyToShip method
     */
    public function testGetQtyToShip()
    {
        $this->model->setData([
            'qty_ordered' => 10,
            'qty_shipped' => 3,
            'qty_refunded' => 2,
            'qty_canceled' => 1
        ]);

        $this->assertEquals(4.0, $this->model->getQtyToShip());
    }

    /**
     * Test getQtyToShip returns 0 for dummy items
     */
    public function testGetQtyToShipForDummyItem()
    {
        $this->model->setData([
            'qty_ordered' => 10,
            'qty_shipped' => 0,
            'qty_refunded' => 0,
            'qty_canceled' => 0
        ]);
        $this->model->setHasChildren(true);
        $this->model->setProductOptions([
            'shipment_type' => ProductAbstractType::SHIPMENT_SEPARATELY
        ]);

        // This is a dummy item for shipment (has children + ship separately)
        $this->assertEquals(0, $this->model->getQtyToShip());
    }

    /**
     * Test getQtyToInvoice returns 0 for dummy items
     */
    public function testGetQtyToInvoiceForDummyItem()
    {
        $this->model->setData([
            'qty_ordered' => 10,
            'qty_invoiced' => 0,
            'qty_canceled' => 0
        ]);
        $this->model->setHasChildren(true);
        $this->model->setProductOptions([
            'product_calculations' => ProductAbstractType::CALCULATE_CHILD
        ]);

        // This is a dummy item for calculation
        $this->assertEquals(0, $this->model->getQtyToInvoice());
    }

    /**
     * Test getQtyToRefund returns 0 for dummy items
     */
    public function testGetQtyToRefundForDummyItem()
    {
        $this->model->setData([
            'qty_invoiced' => 10,
            'qty_refunded' => 3
        ]);
        $this->model->setHasChildren(true);
        $this->model->setProductOptions([
            'product_calculations' => ProductAbstractType::CALCULATE_CHILD
        ]);

        // This is a dummy item for calculation
        $this->assertEquals(0, $this->model->getQtyToRefund());
    }

    /**
     * Test getProduct method when product is already cached
     */
    public function testGetProductWhenCached()
    {
        $product = $this->createMock(CatalogProduct::class);
        $this->model->setProduct($product);

        $this->assertSame($product, $this->model->getProduct());
    }

    /**
     * Test getProduct method when product needs to be loaded
     */
    public function testGetProductWhenNotCached()
    {
        $productId = 123;
        $product = $this->createMock(CatalogProduct::class);

        $productRepository = $this->createMock(ProductRepositoryInterface::class);
        $productRepository->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willReturn($product);

        // Create a new model with mock product repository
        $testModel = $this->objectManager->getObject(Item::class, [
            'orderFactory' => $this->orderFactory,
            'serializer' => $this->serializerMock,
            'productRepository' => $productRepository
        ]);
        $testModel->setProductId($productId);

        $this->assertSame($product, $testModel->getProduct());
        // Call again to verify it's cached
        $this->assertSame($product, $testModel->getProduct());
    }

    /**
     * Test getProduct returns null when product doesn't exist
     */
    public function testGetProductWhenNotFound()
    {
        $productId = 999;

        $productRepository = $this->createMock(ProductRepositoryInterface::class);
        $productRepository->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willThrowException(new NoSuchEntityException(__('Product not found')));

        // Create a new model with mock product repository
        $testModel = $this->objectManager->getObject(Item::class, [
            'orderFactory' => $this->orderFactory,
            'serializer' => $this->serializerMock,
            'productRepository' => $productRepository
        ]);
        $testModel->setProductId($productId);

        $this->assertNull($testModel->getProduct());
    }

    /**
     * Test getStore method with store ID
     */
    public function testGetStoreWithStoreId()
    {
        $storeId = 5;
        $store = $this->createMock(Store::class);

        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($store);

        // Create a new model with mock store manager
        $testModel = $this->objectManager->getObject(Item::class, [
            'orderFactory' => $this->orderFactory,
            'serializer' => $this->serializerMock,
            'storeManager' => $storeManager
        ]);
        $testModel->setStoreId($storeId);

        $this->assertSame($store, $testModel->getStore());
    }

    /**
     * Test getStore method without store ID (default store)
     */
    public function testGetStoreWithoutStoreId()
    {
        $store = $this->createMock(Store::class);

        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->expects($this->once())
            ->method('getStore')
            ->with(null)
            ->willReturn($store);

        // Create a new model with mock store manager
        $testModel = $this->objectManager->getObject(Item::class, [
            'orderFactory' => $this->orderFactory,
            'serializer' => $this->serializerMock,
            'storeManager' => $storeManager
        ]);

        $this->assertSame($store, $testModel->getStore());
    }

    /**
     * Test getForceApplyDiscountToParentItem without parent
     */
    public function testGetForceApplyDiscountToParentItemWithoutParent()
    {
        $typeInstance = $this->createPartialMockWithReflection(
            ProductAbstractType::class,
            ['getForceApplyDiscountToParentItem', 'deleteTypeSpecificData']
        );
        $typeInstance->expects($this->once())
            ->method('getForceApplyDiscountToParentItem')
            ->willReturn(true);

        $product = $this->createMock(CatalogProduct::class);
        $product->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($typeInstance);

        $this->model->setProduct($product);

        $this->assertTrue($this->model->getForceApplyDiscountToParentItem());
    }

    /**
     * Test getForceApplyDiscountToParentItem with parent
     */
    public function testGetForceApplyDiscountToParentItemWithParent()
    {
        $typeInstance = $this->createPartialMockWithReflection(
            ProductAbstractType::class,
            ['getForceApplyDiscountToParentItem', 'deleteTypeSpecificData']
        );
        $typeInstance->expects($this->once())
            ->method('getForceApplyDiscountToParentItem')
            ->willReturn(false);

        $parentProduct = $this->createMock(CatalogProduct::class);
        $parentProduct->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($typeInstance);

        $parentItem = $this->objectManager->getObject(Item::class, []);
        $parentItem->setProduct($parentProduct);

        $this->model->setParentItem($parentItem);

        $this->assertFalse($this->model->getForceApplyDiscountToParentItem());
    }

    /**
     * Test isProcessingAvailable when qty to ship is greater than qty to cancel
     */
    public function testIsProcessingAvailableTrue()
    {
        $this->model->setData([
            'qty_ordered' => 10,
            'qty_shipped' => 2,
            'qty_invoiced' => 5,
            'qty_refunded' => 0,
            'qty_canceled' => 0
        ]);

        // Qty to ship = 10 - 2 - 0 - 0 = 8
        // Qty to cancel = min(10 - 5 - 0, 8) = 5
        // 8 > 5 = true
        $this->assertTrue($this->model->isProcessingAvailable());
    }

    /**
     * Test isProcessingAvailable when qty to ship equals qty to cancel
     */
    public function testIsProcessingAvailableFalse()
    {
        $this->model->setData([
            'qty_ordered' => 10,
            'qty_shipped' => 5,
            'qty_invoiced' => 5,
            'qty_refunded' => 0,
            'qty_canceled' => 0
        ]);

        // Qty to ship = 10 - 5 - 0 - 0 = 5
        // Qty to cancel = min(10 - 5 - 0, 5) = 5
        // 5 > 5 = false
        $this->assertFalse($this->model->isProcessingAvailable());
    }

    /**
     * Test getStatusId with children having backorders
     */
    public function testGetStatusIdWithChildrenBackordered()
    {
        $this->model->setData([
            'qty_ordered' => 10,
            'qty_backordered' => 0,
            'qty_canceled' => 0,
            'qty_refunded' => 0,
            'qty_shipped' => 0,
            'qty_invoiced' => 0
        ]);
        $this->model->setHasChildren(true);

        // Create child items with backorders
        $childItem1 = $this->objectManager->getObject(Item::class, []);
        $childItem1->setQtyBackordered(3);

        $childItem2 = $this->objectManager->getObject(Item::class, []);
        $childItem2->setQtyBackordered(7);

        $this->model->addChildItem([$childItem1, $childItem2]);

        // Total backordered from children = 3 + 7 = 10
        // actuallyOrdered = 10 - 0 - 0 = 10
        // backordered (10) == actuallyOrdered (10) => STATUS_BACKORDERED
        $this->assertEquals(Item::STATUS_BACKORDERED, $this->model->getStatusId());
    }

    /**
     * Test getStatusName when statuses not initialized
     */
    public function testGetStatusNameInitializesStatuses()
    {
        // Use reflection to reset the static $_statuses variable to null
        $reflection = new \ReflectionClass(Item::class);
        $property = $reflection->getProperty('_statuses');
        $property->setAccessible(true);
        $property->setValue(null, null);

        // Now call getStatusName which should trigger initialization
        $statusName = Item::getStatusName(Item::STATUS_RETURNED);
        $this->assertEquals('Returned', $statusName);

        // Verify all statuses are properly initialized
        $statuses = Item::getStatuses();
        $this->assertIsArray($statuses);
        $this->assertArrayHasKey(Item::STATUS_PENDING, $statuses);
        $this->assertArrayHasKey(Item::STATUS_SHIPPED, $statuses);
        $this->assertArrayHasKey(Item::STATUS_INVOICED, $statuses);
        $this->assertArrayHasKey(Item::STATUS_BACKORDERED, $statuses);
        $this->assertArrayHasKey(Item::STATUS_RETURNED, $statuses);
        $this->assertArrayHasKey(Item::STATUS_REFUNDED, $statuses);
        $this->assertArrayHasKey(Item::STATUS_CANCELED, $statuses);
        $this->assertArrayHasKey(Item::STATUS_PARTIAL, $statuses);
        $this->assertArrayHasKey(Item::STATUS_MIXED, $statuses);
    }
}
