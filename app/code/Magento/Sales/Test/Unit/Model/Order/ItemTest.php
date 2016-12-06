<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\ResourceModel\OrderFactory;
use \Magento\Sales\Model\Order;

/**
 * Class ItemTest
 *
 * @package Magento\Sales\Model\Order
 */
class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Item
     */
    protected $model;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var OrderFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactory;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->orderFactory = $this->getMock(\Magento\Sales\Model\OrderFactory::class, ['create'], [], '', false);

        $arguments = [
            'orderFactory' => $this->orderFactory,
        ];
        $this->model = $this->objectManager->getObject(\Magento\Sales\Model\Order\Item::class, $arguments);
    }

    public function testSetParentItemNull()
    {
        $this->assertEquals($this->model, $this->model->setParentItem(null));
        $this->assertNull($this->model->getParentItem());
    }

    public function testSetParentItem()
    {
        $item = $this->objectManager->getObject(\Magento\Sales\Model\Order\Item::class, []);
        $this->assertEquals($this->model, $this->model->setParentItem($item));
        $this->assertEquals($item, $this->model->getParentItem());
        $this->assertTrue($item->getHasChildren());
        $this->assertCount(1, $item->getChildrenItems());
    }

    public function testGetPatentItem()
    {
        $item = $this->objectManager->getObject(\Magento\Sales\Model\Order\Item::class, []);
        $this->model->setData(\Magento\Sales\Api\Data\OrderItemInterface::PARENT_ITEM, $item);
        $this->assertEquals($item, $this->model->getParentItem());
    }

    public function testSetOrder()
    {
        $orderId = 123;
        $order = $this->getMock(\Magento\Sales\Model\Order::class, [], [], '', false);
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
        $order = $this->getMock(\Magento\Sales\Model\Order::class, [], [], '', false);
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
    )
    {
        $this->model->setQtyBackordered($qtyBackOrdered);
        $this->model->setQtyCanceled($qtyCanceled);
        $this->model->setQtyInvoiced($qtyInvoiced);
        $this->model->setQtyOrdered($qtyOrdered);
        $this->model->setQtyRefunded($qtyRefunded);
        $this->model->setQtyShipped($qtyShipped);

        $this->assertEquals($expectedStatus, $this->model->getStatusId());
    }

    public function getStatusIdDataProvider()
    {
        return [
            [0, 0, 0, null, 0, 0, \Magento\Sales\Model\Order\Item::STATUS_PENDING],
            [0, 10, 1, 100, 10, 80, \Magento\Sales\Model\Order\Item::STATUS_SHIPPED],
            [1, 10, 1, 100, 10, 80, \Magento\Sales\Model\Order\Item::STATUS_SHIPPED],
            [1, 10, 1, 100, 10, 99, \Magento\Sales\Model\Order\Item::STATUS_MIXED],
            [0, 10, 80, 100, 10, 0, \Magento\Sales\Model\Order\Item::STATUS_INVOICED],
            [1, 10, 80, 100, 10, 0, \Magento\Sales\Model\Order\Item::STATUS_INVOICED],
            [1, 10, 99, 100, 10, 0, \Magento\Sales\Model\Order\Item::STATUS_MIXED],
            [80, 10, null, 100, 10, null, \Magento\Sales\Model\Order\Item::STATUS_BACKORDERED],
            [null, null, null, 9, 9, null, \Magento\Sales\Model\Order\Item::STATUS_REFUNDED],
            [null, 9, null, 9, null, null, \Magento\Sales\Model\Order\Item::STATUS_CANCELED],
            [1, 10, 70, 100, 10, 79, \Magento\Sales\Model\Order\Item::STATUS_PARTIAL],
            [0, 10, 70, 100, 10, 79, \Magento\Sales\Model\Order\Item::STATUS_PARTIAL]
        ];
    }

    public function testGetStatuses()
    {
        $statuses = [
            \Magento\Sales\Model\Order\Item::STATUS_PENDING => 'Ordered',
            \Magento\Sales\Model\Order\Item::STATUS_SHIPPED => 'Shipped',
            \Magento\Sales\Model\Order\Item::STATUS_INVOICED => 'Invoiced',
            \Magento\Sales\Model\Order\Item::STATUS_BACKORDERED => 'Backordered',
            \Magento\Sales\Model\Order\Item::STATUS_RETURNED => 'Returned',
            \Magento\Sales\Model\Order\Item::STATUS_REFUNDED => 'Refunded',
            \Magento\Sales\Model\Order\Item::STATUS_CANCELED => 'Canceled',
            \Magento\Sales\Model\Order\Item::STATUS_PARTIAL => 'Partial',
            \Magento\Sales\Model\Order\Item::STATUS_MIXED => 'Mixed',
        ];
        $this->assertEquals($statuses, $this->model->getStatuses());
    }

    public function testGetOriginalPrice()
    {
        $price = 9.99;
        $this->model->setPrice($price);
        $this->assertEquals($price, $this->model->getOriginalPrice());

        $originalPrice = 5.55;
        $this->model->setData(\Magento\Sales\Api\Data\OrderItemInterface::ORIGINAL_PRICE, $originalPrice);
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
        $serializerMock = $this->getMock(SerializerInterface::class, [], ['unserialize'], '', false);
        if (is_string($options)) {
            $serializerMock->expects($this->once())->method('unserialize')->will($this->returnValue($expectedResult));
        }
        $this->objectManager->setBackwardCompatibleProperty($this->model, 'serializer', $serializerMock);
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
}
