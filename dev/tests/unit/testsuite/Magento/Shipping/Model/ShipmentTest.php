<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Model;

use Magento\Sales\Model\Resource\OrderFactory;
use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ShipmentTest
 */
class ShipmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrderFactory |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactory;

    /**
     * @var \Magento\Sales\Model\Order\Shipment
     */
    protected $shipment;

    public function setUp()
    {
        $this->orderFactory = $this->getMock(
            '\Magento\Sales\Model\OrderFactory',
            ['create'],
            [],
            '',
            false
        );

        $objectManagerHelper = new ObjectManagerHelper($this);
        $arguments = [
            'context' => $this->getMock('Magento\Framework\Model\Context', [], [], '', false),
            'registry' => $this->getMock('Magento\Framework\Registry', [], [], '', false),
            'localeDate' => $this->getMock('Magento\Framework\Stdlib\DateTime\TimezoneInterface', [], [], '', false),
            'dateTime' => $this->getMock('Magento\Framework\Stdlib\DateTime', [], [], '', false),
            'orderFactory' => $this->orderFactory,
            'shipmentItemCollectionFactory' => $this->getMock(
                    'Magento\Sales\Model\Resource\Order\Shipment\Item\CollectionFactory',
                    [],
                    [],
                    '',
                    false
                ),
            'trackCollectionFactory' => $this->getMock(
                    'Magento\Sales\Model\Resource\Order\Shipment\Track\CollectionFactory',
                    [],
                    [],
                    '',
                    false
                ),
            'commentFactory' => $this->getMock(
                    'Magento\Sales\Model\Order\Shipment\CommentFactory',
                    [],
                    [],
                    '',
                    false
                ),
            'commentCollectionFactory' => $this->getMock(
                    'Magento\Sales\Model\Resource\Order\Shipment\Comment\CollectionFactory',
                    [],
                    [],
                    '',
                    false
                ),
        ];
        $this->shipment = $objectManagerHelper->getObject(
            'Magento\Sales\Model\Order\Shipment',
            $arguments
        );
    }

    public function testGetOrder()
    {
        $orderId = 100000041;
        $this->shipment->setOrderId($orderId);
        $entityName = 'shipment';
        $order = $this->getMock(
            'Magento\Sales\Model\Order',
            ['load', 'setHistoryEntityName', '__wakeUp'],
            [],
            '',
            false
        );
        $this->shipment->setOrderId($orderId);
        $order->expects($this->atLeastOnce())
            ->method('setHistoryEntityName')
            ->with($entityName)
            ->will($this->returnSelf());
        $order->expects($this->atLeastOnce())
            ->method('load')
            ->with($orderId)
            ->will($this->returnValue($order));

        $this->orderFactory->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($order));

        $this->assertEquals($order, $this->shipment->getOrder());
    }

    public function testGetEntityType()
    {
        $this->assertEquals('shipment', $this->shipment->getEntityType());
    }
}
