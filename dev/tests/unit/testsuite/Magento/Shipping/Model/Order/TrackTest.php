<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Order;

class TrackTest extends \PHPUnit_Framework_TestCase
{
    public function testLookup()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $carrier = $this->getMock(
            'Magento\OfflineShipping\Model\Carrier\Freeshipping',
            ['setStore', 'getTrackingInfo'],
            [],
            '',
            false
        );
        $carrier->expects($this->once())->method('setStore')->with('');
        $carrier->expects($this->once())->method('getTrackingInfo')->will($this->returnValue('trackingInfo'));

        $carrierFactory = $this->getMock(
            '\Magento\Shipping\Model\CarrierFactory',
            ['create'],
            [],
            '',
            false
        );
        $carrierFactory->expects($this->once())->method('create')->will($this->returnValue($carrier));

        $shipment = $this->getMock(
            'Magento\OfflineShipping\Model\Carrier\Freeshipping',
            ['load'],
            [],
            '',
            false
        );
        $shipment->expects($this->any())->method('load')->will($this->returnValue($shipment));

        $shipmentFactory = $this->getMock(
            '\Magento\Sales\Model\Order\ShipmentFactory',
            ['create'],
            [],
            '',
            false
        );
        $shipmentFactory->expects($this->any())->method('create')->will($this->returnValue($shipment));

        /** @var \Magento\Shipping\Model\Order\Track $model */
        $model = $helper->getObject(
            'Magento\Shipping\Model\Order\Track',
            ['carrierFactory' => $carrierFactory, 'shipmentFactory' => $shipmentFactory]
        );

        $this->assertEquals('trackingInfo', $model->getNumberDetail());
    }
}
