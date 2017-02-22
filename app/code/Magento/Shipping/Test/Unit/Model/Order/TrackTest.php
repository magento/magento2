<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Model\Order;

class TrackTest extends \PHPUnit_Framework_TestCase
{
    public function testLookup()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

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
            [],
            [],
            '',
            false
        );

        $shipmentRepository = $this->getMock(
            'Magento\Sales\Model\Order\ShipmentRepository',
            ['get'],
            [],
            '',
            false
        );
        $shipmentRepository->expects($this->any())->method('get')->willReturn($shipment);

        /** @var \Magento\Shipping\Model\Order\Track $model */
        $model = $helper->getObject(
            'Magento\Shipping\Model\Order\Track',
            ['carrierFactory' => $carrierFactory, 'shipmentRepository' => $shipmentRepository]
        );

        $this->assertEquals('trackingInfo', $model->getNumberDetail());
    }
}
