<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Model\Order;

class TrackTest extends \PHPUnit_Framework_TestCase
{
    public function testLookup()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $carrier = $this->getMock(
            \Magento\OfflineShipping\Model\Carrier\Freeshipping::class,
            ['setStore', 'getTrackingInfo'],
            [],
            '',
            false
        );
        $carrier->expects($this->once())->method('setStore')->with('');
        $carrier->expects($this->once())->method('getTrackingInfo')->will($this->returnValue('trackingInfo'));

        $carrierFactory = $this->getMock(
            \Magento\Shipping\Model\CarrierFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $carrierFactory->expects($this->once())->method('create')->will($this->returnValue($carrier));

        $shipment = $this->getMock(
            \Magento\OfflineShipping\Model\Carrier\Freeshipping::class,
            [],
            [],
            '',
            false
        );

        $shipmentRepository = $this->getMock(
            \Magento\Sales\Model\Order\ShipmentRepository::class,
            ['get'],
            [],
            '',
            false
        );
        $shipmentRepository->expects($this->any())->method('get')->willReturn($shipment);

        /** @var \Magento\Shipping\Model\Order\Track $model */
        $model = $helper->getObject(
            \Magento\Shipping\Model\Order\Track::class,
            ['carrierFactory' => $carrierFactory, 'shipmentRepository' => $shipmentRepository]
        );
        $model->setParentId(1);
        $this->assertEquals('trackingInfo', $model->getNumberDetail());
    }
}
