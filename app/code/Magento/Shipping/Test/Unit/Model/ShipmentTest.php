<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Test\Unit\Model;

use \Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ShipmentTest
 */
class ShipmentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Model\Order\Shipment
     */
    protected $shipment;

    protected function setUp()
    {
        $this->orderRepository = $this->createMock(\Magento\Sales\Api\OrderRepositoryInterface::class);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $arguments = [
            'context' => $this->createMock(\Magento\Framework\Model\Context::class),
            'registry' => $this->createMock(\Magento\Framework\Registry::class),
            'localeDate' => $this->createMock(
                \Magento\Framework\Stdlib\DateTime\TimezoneInterface::class
            ),
            'dateTime' => $this->createMock(\Magento\Framework\Stdlib\DateTime::class),
            'orderRepository' => $this->orderRepository,
            'shipmentItemCollectionFactory' => $this->createMock(
                \Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory::class
            ),
            'trackCollectionFactory' => $this->createMock(
                \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory::class
            ),
            'commentFactory' => $this->createMock(\Magento\Sales\Model\Order\Shipment\CommentFactory::class),
            'commentCollectionFactory' => $this->createMock(
                \Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory::class
            ),
        ];
        $this->shipment = $objectManagerHelper->getObject(
            \Magento\Sales\Model\Order\Shipment::class,
            $arguments
        );
    }

    public function testGetOrder()
    {
        $orderId = 100000041;
        $this->shipment->setOrderId($orderId);
        $entityName = 'shipment';
        $order = $this->createPartialMock(
            \Magento\Sales\Model\Order::class,
            ['load', 'setHistoryEntityName', '__wakeUp']
        );
        $this->shipment->setOrderId($orderId);
        $order->expects($this->atLeastOnce())
            ->method('setHistoryEntityName')
            ->with($entityName)
            ->will($this->returnSelf());

        $this->orderRepository->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnValue($order));

        $this->assertEquals($order, $this->shipment->getOrder());
    }

    public function testGetEntityType()
    {
        $this->assertEquals('shipment', $this->shipment->getEntityType());
    }
}
